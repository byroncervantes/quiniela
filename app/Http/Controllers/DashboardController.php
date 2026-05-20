<?php

namespace App\Http\Controllers;

use App\Models\Pool;
use App\Models\PoolParticipant;
use App\Models\Prediction;
use App\Models\GameMatch;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\AppSetting;
use App\Models\ScoringRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                return redirect()->route('login');
            }
            if (Auth::user()->status !== 'active') {
                Auth::logout();
                return redirect()->route('login')->withErrors(['email' => 'Tu cuenta no está activa.']);
            }
            return $next($request);
        });
    }

    /**
     * View Participant Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Fetch pools user is participating in
        $joinedParticipants = PoolParticipant::where('user_id', $user->id)
            ->with('pool.tournament')
            ->get();

        $joinedPoolIds = $joinedParticipants->pluck('pool_id')->toArray();

        // Fetch other active public pools that the user hasn't joined yet
        $availablePools = Pool::where('is_active', true)
            ->whereNotIn('id', $joinedPoolIds)
            ->where('visibility', 'public')
            ->with('tournament')
            ->get();

        return view('dashboard', compact('user', 'joinedParticipants', 'availablePools'));
    }

    /**
     * Join a public pool
     */
    public function joinPool(Request $request, $id)
    {
        $user = Auth::user();
        $pool = Pool::where('id', $id)->where('is_active', true)->firstOrFail();

        // Check if already in it
        $existing = PoolParticipant::where('pool_id', $pool->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return back()->with('info', 'Ya estás registrado en esta quiniela.');
        }

        // Handle private/code verification if required
        if ($pool->join_mode === 'invitation_code') {
            $request->validate([
                'invitation_code' => 'required|string',
            ]);
            if ($request->invitation_code !== $pool->invitation_code) {
                return back()->withErrors(['invitation_code' => 'El código de invitación ingresado es incorrecto.']);
            }
        }

        $status = ($pool->join_mode === 'approval_required') ? 'pending' : 'approved';

        PoolParticipant::create([
            'pool_id' => $pool->id,
            'user_id' => $user->id,
            'status' => $status,
            'joined_at' => now(),
        ]);

        if ($status === 'pending') {
            return redirect()->route('dashboard')->with('success', 'Solicitud enviada. Esta quiniela requiere aprobación del administrador.');
        }

        return redirect()->route('dashboard')->with('success', '¡Te has unido exitosamente a la quiniela ' . $pool->name . '!');
    }

    /**
     * View/Edit predictions inside a pool
     */
    public function predictions($slug)
    {
        $user = Auth::user();
        $pool = Pool::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Verify user is an approved participant
        $participant = PoolParticipant::where('pool_id', $pool->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->firstOrFail();

        // Get tournament and matches
        $tournament = $pool->tournament;
        $matches = GameMatch::where('tournament_id', $tournament->id)
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('starts_at', 'asc')
            ->orderBy('match_number', 'asc')
            ->get();

        // Group matches by stages/rounds for neat mobile tabs
        $stages = [
            'group_stage' => 'Fase de Grupos',
            'round_of_32' => 'Dieciseisavos de Final',
            'round_of_16' => 'Octavos de Final',
            'quarter_final' => 'Cuartos de Final',
            'semi_final' => 'Semifinales',
            'third_place' => 'Tercer Lugar',
            'final' => 'Gran Final',
        ];

        // Fetch user's existing predictions in this pool
        $predictions = Prediction::where('pool_id', $pool->id)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('match_id');

        return view('predictions', compact('pool', 'participant', 'matches', 'stages', 'predictions'));
    }

    /**
     * Save a single prediction via AJAX (highly premium feature)
     */
    public function savePredictionAjax(Request $request, $slug)
    {
        $user = Auth::user();
        $pool = Pool::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Verify participant
        $participant = PoolParticipant::where('pool_id', $pool->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        if (!$participant) {
            return response()->json(['error' => 'No estás aprobado en esta quiniela.'], 403);
        }

        $request->validate([
            'match_id' => 'required|exists:matches,id',
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
            'home_penalty_score' => 'nullable|integer|min:0',
            'away_penalty_score' => 'nullable|integer|min:0',
            'predicted_winner_team_id' => 'nullable|exists:teams,id',
        ]);

        $match = GameMatch::findOrFail($request->match_id);

        // Security check: Lock time
        if ($match->isPredictionsLocked()) {
            return response()->json(['error' => 'Las predicciones para este partido ya están cerradas.'], 400);
        }

        $prediction = Prediction::updateOrCreate(
            [
                'pool_id' => $pool->id,
                'user_id' => $user->id,
                'match_id' => $match->id,
            ],
            [
                'predicted_home_score' => $request->home_score,
                'predicted_away_score' => $request->away_score,
                'predicted_home_penalty_score' => $request->home_penalty_score,
                'predicted_away_penalty_score' => $request->away_penalty_score,
                'predicted_winner_team_id' => $request->predicted_winner_team_id,
                'submitted_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'prediction_id' => $prediction->id]);
    }

    /**
     * Bulk save predictions (fallback/traditional method)
     */
    public function savePredictionsBulk(Request $request, $slug)
    {
        $user = Auth::user();
        $pool = Pool::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Verify participant
        $participant = PoolParticipant::where('pool_id', $pool->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->firstOrFail();

        $predData = $request->input('predictions', []);
        $savedCount = 0;
        $failedCount = 0;

        foreach ($predData as $matchId => $scores) {
            $homeScore = $scores['home'] ?? null;
            $awayScore = $scores['away'] ?? null;

            if ($homeScore === null || $homeScore === '' || $awayScore === null || $awayScore === '') {
                continue; // Skip incomplete forms
            }

            $match = GameMatch::find($matchId);
            if (!$match || $match->isPredictionsLocked()) {
                $failedCount++;
                continue;
            }

            Prediction::updateOrCreate(
                [
                    'pool_id' => $pool->id,
                    'user_id' => $user->id,
                    'match_id' => $match->id,
                ],
                [
                    'predicted_home_score' => intval($homeScore),
                    'predicted_away_score' => intval($awayScore),
                    'predicted_home_penalty_score' => isset($scores['home_penalties']) && $scores['home_penalties'] !== '' ? intval($scores['home_penalties']) : null,
                    'predicted_away_penalty_score' => isset($scores['away_penalties']) && $scores['away_penalties'] !== '' ? intval($scores['away_penalties']) : null,
                    'predicted_winner_team_id' => $scores['predicted_winner'] ?? null,
                    'submitted_at' => now(),
                ]
            );

            $savedCount++;
        }

        $msg = "Se guardaron {$savedCount} pronósticos exitosamente.";
        if ($failedCount > 0) {
            $msg .= " No se pudieron guardar {$failedCount} porque los partidos ya iniciaron.";
            return redirect()->back()->with('warning', $msg);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * View Leaders Ranking for a specific Pool
     */
    public function ranking($slug)
    {
        $pool = Pool::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        // Settings check for ranking visibility
        if (!AppSetting::getValue('public_rankings_enabled', true)) {
            // Only allow if participant is approved
            $isApproved = PoolParticipant::where('pool_id', $pool->id)
                ->where('user_id', Auth::id())
                ->where('status', 'approved')
                ->exists();

            if (!$isApproved && !in_array(Auth::user()->role, ['super_admin', 'admin_quiniela'])) {
                return redirect()->route('dashboard')->with('error', 'La visualización de rankings públicos está deshabilitada.');
            }
        }

        // Fetch participants ordered by their official rank
        $participants = PoolParticipant::where('pool_id', $pool->id)
            ->where('status', 'approved')
            ->with('user')
            ->orderBy(DB::raw('COALESCE(current_rank, 9999)'), 'asc')
            ->orderBy('total_points', 'desc')
            ->get();

        return view('ranking', compact('pool', 'participants'));
    }

    /**
     * View Matches Schedule & User Results
     */
    public function matches()
    {
        $user = Auth::user();
        
        // Fetch all active matches in the system
        $matches = GameMatch::with(['homeTeam', 'awayTeam', 'tournament'])
            ->orderBy('starts_at', 'asc')
            ->orderBy('match_number', 'asc')
            ->get();

        // Get predictions mapped by pool and match
        $predictions = Prediction::where('user_id', $user->id)
            ->with('pool')
            ->get()
            ->groupBy('pool_id');

        return view('matches', compact('matches', 'predictions'));
    }

    /**
     * View Rules page
     */
    public function rules()
    {
        $rules = ScoringRule::where('is_active', true)->get();
        $termsText = AppSetting::getValue('terms_and_conditions_text', '');
        return view('rules', compact('rules', 'termsText'));
    }

    /**
     * Edit Profile page
     */
    public function showProfile()
    {
        $user = Auth::user();
        
        $branches = [
            'Central - Ciudad de Guatemala',
            'Sucursal Villa Nueva',
            'Sucursal Quetzaltenango',
            'Sucursal Chiquimula',
            'Sucursal Escuintla',
            'Sucursal Cobán',
            'Sucursal Petén',
            'Sucursal Zacapa',
            'Sucursal Mazatenango',
            'Distribución / Bodega Central'
        ];

        $departments = [
            'Ventas / Ventas Rutas',
            'Administración',
            'Logística / Despacho / Bodega',
            'Contabilidad y Finanzas',
            'Recursos Humanos',
            'Sistemas / IT',
            'Créditos y Cobros',
            'Operaciones',
            'Servicio al Cliente',
            'Mercadeo',
            'Auditoría Interna'
        ];

        return view('profile', compact('user', 'branches', 'departments'));
    }

    /**
     * Update Profile action
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'department' => 'required|string|max:100',
            'branch' => 'required|string|max:100',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->department = $request->department;
        $user->branch = $request->branch;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Perfil actualizado con éxito.');
    }
}
