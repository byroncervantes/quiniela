<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\Team;
use App\Models\GameMatch;
use App\Models\ScoringRule;
use App\Models\Pool;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeedWorldCup2026 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quinmariscal:seed-worldcup-2026';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga el torneo Mundial FIFA 2026 con 12 grupos, 48 selecciones, reglas iniciales y 104 partidos.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando carga de la Quiniela Mundial FIFA 2026...');

        // 1. App Settings
        $this->info('Cargando configuraciones iniciales de La Quiniela de Todos...');
        AppSetting::setValue('registration_enabled', true, 'boolean', 'general', 'Habilitar autoregistro de usuarios');
        AppSetting::setValue('registration_requires_approval', false, 'boolean', 'general', 'Requiere aprobación de administrador para registro');
        AppSetting::setValue('allowed_email_domains', '', 'string', 'general', 'Dominios de email permitidos (separados por coma, ej: distmariscal.com,mariscal.gt). Dejar vacío para registro abierto.');
        AppSetting::setValue('require_invitation_code', false, 'boolean', 'general', 'Requiere código de invitación para unirse a quinielas');
        AppSetting::setValue('public_rankings_enabled', true, 'boolean', 'general', 'Permitir visualización de rankings públicos');
        AppSetting::setValue('predictions_close_minutes_before_match', 0, 'integer', 'general', 'Minutos antes del inicio del partido para cerrar pronósticos');
        AppSetting::setValue('terms_and_conditions_text', 'Esta quiniela es una actividad recreativa interna. La participación no implica apuestas monetarias obligatorias. Las reglas, premios simbólicos y criterios de desempate serán definidos por la administración.', 'string', 'general', 'Texto de términos y condiciones');
        AppSetting::setValue('welcome_message', '¡Bienvenido a La Quiniela de Todos! La quiniela mundialista oficial de Distribuidora Mariscal.', 'string', 'general', 'Mensaje de bienvenida');

        // 2. Tournament
        $this->info('Creando Torneo...');
        $tournament = Tournament::updateOrCreate(
            ['slug' => 'mundial-fifa-2026'],
            [
                'name' => 'Mundial FIFA 2026',
                'description' => 'La Quiniela de Todos - Quiniela oficial de Distribuidora Mariscal para el Mundial de Fútbol FIFA 2026 (EE.UU., Canadá y México).',
                'year' => 2026,
                'status' => 'active',
                'starts_at' => '2026-06-11 12:00:00',
                'ends_at' => '2026-07-19 18:00:00',
                'is_active' => true,
            ]
        );

        // 3. Scoring Rules
        $this->info('Creando Reglas de Puntuación...');
        $rules = [
            ['key' => 'exact_score', 'name' => 'Marcador Exacto', 'points' => 3, 'description' => 'Acertar los goles exactos del local y del visitante.'],
            ['key' => 'correct_winner', 'name' => 'Ganador Correcto', 'points' => 1, 'description' => 'Acertar el equipo ganador sin acertar el marcador exacto.'],
            ['key' => 'correct_draw', 'name' => 'Empate Correcto', 'points' => 1, 'description' => 'Acertar que el partido termina en empate sin acertar el marcador exacto.'],
            ['key' => 'knockout_winner', 'name' => 'Ganador de Eliminación', 'points' => 1, 'description' => 'Acertar el equipo que pasa de ronda en fases eliminatorias (tiempo extra/penales).'],
        ];

        foreach ($rules as $r) {
            ScoringRule::updateOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'key' => $r['key']
                ],
                [
                    'name' => $r['name'],
                    'points' => $r['points'],
                    'description' => $r['description'],
                    'is_active' => true,
                ]
            );
        }

        // 4. Create an Admin user if none exists
        $admin = User::where('role', 'super_admin')->first();
        if (!$admin) {
            $branch = \App\Models\Branch::firstOrCreate(
                ['name' => 'Central - Ciudad de Guatemala'],
                ['is_active' => true]
            );
            $department = \App\Models\Department::firstOrCreate(
                ['name' => 'Administración'],
                ['is_active' => true]
            );
            $admin = User::updateOrCreate(
                ['email' => 'admin@laquinieladetodos.local'],
                [
                    'name' => 'Administrador La Quiniela de Todos',
                    'password' => bcrypt('password'),
                    'role' => 'super_admin',
                    'status' => 'active',
                    'phone' => '12345678',
                    'employee_code' => '1000000000001',
                    'department_id' => $department->id,
                    'branch_id' => $branch->id,
                    'company' => 'Distribuidora Mariscal',
                    'accepted_terms' => true,
                ]
            );
            $this->info('Usuario Administrador inicial creado (admin@laquinieladetodos.local / password).');
        }

        // 5. Default Pool
        $this->info('Creando la Quiniela General...');
        $pool = Pool::updateOrCreate(
            ['slug' => 'quiniela-general-mariscal'],
            [
                'tournament_id' => $tournament->id,
                'name' => 'La Quiniela de Todos',
                'description' => 'La quiniela general abierta para todos los colaboradores.',
                'visibility' => 'public',
                'join_mode' => 'open',
                'is_active' => true,
                'created_by' => $admin->id,
                'starts_at' => '2026-06-11 12:00:00',
                'ends_at' => '2026-07-19 18:00:00',
            ]
        );

        // 6. Clear existing predictions, matches, teams, and groups to avoid unique constraint violations
        $this->info('Limpiando tablas de datos anteriores (predicciones, partidos, selecciones, grupos)...');
        Schema::disableForeignKeyConstraints();
        \App\Models\Prediction::truncate();
        GameMatch::truncate();
        Team::truncate();
        TournamentGroup::truncate();
        Schema::enableForeignKeyConstraints();

        // 7. Load wc2026.json
        $jsonPath = database_path('data/wc2026.json');
        if (!file_exists($jsonPath)) {
            $this->error("No se encontró el archivo JSON en: {$jsonPath}");
            return Command::FAILURE;
        }

        $wcData = json_decode(file_get_contents($jsonPath), true);
        $matchesList = $wcData['matches'] ?? [];

        $this->info('Cargando grupos, selecciones y partidos desde el JSON oficial...');

        $teamDetails = [
            'Mexico' => ['fifa' => 'MEX', 'cc' => 'mx'],
            'South Africa' => ['fifa' => 'RSA', 'cc' => 'za'],
            'South Korea' => ['fifa' => 'KOR', 'cc' => 'kr'],
            'Czech Republic' => ['fifa' => 'CZE', 'cc' => 'cz'],
            'Canada' => ['fifa' => 'CAN', 'cc' => 'ca'],
            'Bosnia & Herzegovina' => ['fifa' => 'BIH', 'cc' => 'ba'],
            'Qatar' => ['fifa' => 'QAT', 'cc' => 'qa'],
            'Switzerland' => ['fifa' => 'SUI', 'cc' => 'ch'],
            'Brazil' => ['fifa' => 'BRA', 'cc' => 'br'],
            'Morocco' => ['fifa' => 'MAR', 'cc' => 'ma'],
            'Haiti' => ['fifa' => 'HAI', 'cc' => 'ht'],
            'Scotland' => ['fifa' => 'SCO', 'cc' => 'gb-sct'],
            'USA' => ['fifa' => 'USA', 'cc' => 'us'],
            'Paraguay' => ['fifa' => 'PAR', 'cc' => 'py'],
            'Australia' => ['fifa' => 'AUS', 'cc' => 'au'],
            'Turkey' => ['fifa' => 'TUR', 'cc' => 'tr'],
            'Germany' => ['fifa' => 'GER', 'cc' => 'de'],
            'Curaçao' => ['fifa' => 'CUW', 'cc' => 'cw'],
            'Ivory Coast' => ['fifa' => 'CIV', 'cc' => 'ci'],
            'Ecuador' => ['fifa' => 'ECU', 'cc' => 'ec'],
            'Netherlands' => ['fifa' => 'NED', 'cc' => 'nl'],
            'Japan' => ['fifa' => 'JPN', 'cc' => 'jp'],
            'Sweden' => ['fifa' => 'SWE', 'cc' => 'se'],
            'Tunisia' => ['fifa' => 'TUN', 'cc' => 'tn'],
            'Belgium' => ['fifa' => 'BEL', 'cc' => 'be'],
            'Egypt' => ['fifa' => 'EGY', 'cc' => 'eg'],
            'Iran' => ['fifa' => 'IRN', 'cc' => 'ir'],
            'New Zealand' => ['fifa' => 'NZL', 'cc' => 'nz'],
            'Spain' => ['fifa' => 'ESP', 'cc' => 'es'],
            'Cape Verde' => ['fifa' => 'CPV', 'cc' => 'cv'],
            'Saudi Arabia' => ['fifa' => 'KSA', 'cc' => 'sa'],
            'Uruguay' => ['fifa' => 'URU', 'cc' => 'uy'],
            'France' => ['fifa' => 'FRA', 'cc' => 'fr'],
            'Senegal' => ['fifa' => 'SEN', 'cc' => 'sn'],
            'Norway' => ['fifa' => 'NOR', 'cc' => 'no'],
            'Iraq' => ['fifa' => 'IRQ', 'cc' => 'iq'],
            'Argentina' => ['fifa' => 'ARG', 'cc' => 'ar'],
            'Algeria' => ['fifa' => 'ALG', 'cc' => 'dz'],
            'Austria' => ['fifa' => 'AUT', 'cc' => 'at'],
            'Jordan' => ['fifa' => 'JOR', 'cc' => 'jo'],
            'Portugal' => ['fifa' => 'POR', 'cc' => 'pt'],
            'DR Congo' => ['fifa' => 'COD', 'cc' => 'cd'],
            'Uzbekistan' => ['fifa' => 'UZB', 'cc' => 'uz'],
            'Colombia' => ['fifa' => 'COL', 'cc' => 'co'],
            'England' => ['fifa' => 'ENG', 'cc' => 'gb-eng'],
            'Croatia' => ['fifa' => 'CRO', 'cc' => 'hr'],
            'Ghana' => ['fifa' => 'GHA', 'cc' => 'gh'],
            'Panama' => ['fifa' => 'PAN', 'cc' => 'pa'],
        ];

        $stageMapping = [
            'Round of 32' => 'round_of_32',
            'Round of 16' => 'round_of_16',
            'Quarter-final' => 'quarter_final',
            'Semi-final' => 'semi_final',
            'Match for third place' => 'third_place',
            'Final' => 'final'
        ];

        $groupsCreated = [];
        $teamsCreated = [];

        foreach ($matchesList as $index => $matchData) {
            $matchNumber = $index + 1;
            $round = $matchData['round'] ?? 'Matchday';
            $stage = $stageMapping[$round] ?? 'group_stage';

            // Parse Date and Time with timezone offset
            $date = $matchData['date'];
            $timeString = $matchData['time'];
            $parsedDateTime = null;

            if (preg_match('/(\d{2}:\d{2})\s+UTC([+-]\d+)/', $timeString, $matchesOffset)) {
                $time = $matchesOffset[1];
                $offset = intval($matchesOffset[2]);
                $sign = $offset >= 0 ? '+' : '-';
                $absOffset = abs($offset);
                $offsetStr = sprintf('%s%02d:00', $sign, $absOffset);
                $parsedDateTime = Carbon::createFromFormat('Y-m-d H:i P', $date . ' ' . $time . ' ' . $offsetStr);
            } else {
                $parsedDateTime = Carbon::parse($date . ' ' . $timeString);
            }

            // Normalize to application timezone (e.g. America/Guatemala)
            $parsedDateTime->setTimezone(config('app.timezone', 'America/Guatemala'));

            if (!empty($matchData['group'])) {
                // Group Stage Match
                $groupCode = trim(str_replace('Group', '', $matchData['group']));

                if (!isset($groupsCreated[$groupCode])) {
                    $groupsCreated[$groupCode] = TournamentGroup::firstOrCreate(
                        ['tournament_id' => $tournament->id, 'code' => $groupCode],
                        [
                            'name' => "Grupo {$groupCode}",
                            'order' => ord($groupCode) - ord('A') + 1
                        ]
                    );
                }
                $group = $groupsCreated[$groupCode];

                // Load or create team 1
                $team1Name = $matchData['team1'];
                if (!isset($teamsCreated[$team1Name])) {
                    $details = $teamDetails[$team1Name] ?? ['fifa' => strtoupper(substr($team1Name, 0, 3)), 'cc' => 'un'];
                    $teamsCreated[$team1Name] = Team::firstOrCreate(
                        ['tournament_id' => $tournament->id, 'fifa_code' => $details['fifa']],
                        [
                            'tournament_group_id' => $group->id,
                            'name' => $team1Name,
                            'official_name' => "Selección de " . $team1Name,
                            'country_code' => strtoupper($details['cc']),
                            'flag_url' => "https://flagcdn.com/w160/" . strtolower($details['cc']) . ".png",
                            'is_active' => true,
                        ]
                    );
                }
                $t1 = $teamsCreated[$team1Name];

                // Load or create team 2
                $team2Name = $matchData['team2'];
                if (!isset($teamsCreated[$team2Name])) {
                    $details = $teamDetails[$team2Name] ?? ['fifa' => strtoupper(substr($team2Name, 0, 3)), 'cc' => 'un'];
                    $teamsCreated[$team2Name] = Team::firstOrCreate(
                        ['tournament_id' => $tournament->id, 'fifa_code' => $details['fifa']],
                        [
                            'tournament_group_id' => $group->id,
                            'name' => $team2Name,
                            'official_name' => "Selección de " . $team2Name,
                            'country_code' => strtoupper($details['cc']),
                            'flag_url' => "https://flagcdn.com/w160/" . strtolower($details['cc']) . ".png",
                            'is_active' => true,
                        ]
                    );
                }
                $t2 = $teamsCreated[$team2Name];

                // Create the match
                GameMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_group_id' => $group->id,
                    'home_team_id' => $t1->id,
                    'away_team_id' => $t2->id,
                    'stage' => $stage,
                    'match_number' => $matchNumber,
                    'stadium' => $matchData['ground'] ?? 'Estadio',
                    'city' => $matchData['ground'] ?? 'Sede',
                    'country' => 'Norteamérica 2026',
                    'starts_at' => $parsedDateTime,
                    'status' => 'scheduled',
                ]);

            } else {
                // Knockout Stage Match (with placeholders)
                GameMatch::create([
                    'tournament_id' => $tournament->id,
                    'tournament_group_id' => null,
                    'home_team_id' => null,
                    'away_team_id' => null,
                    'home_placeholder' => $matchData['team1'],
                    'away_placeholder' => $matchData['team2'],
                    'stage' => $stage,
                    'match_number' => $matchNumber,
                    'stadium' => $matchData['ground'] ?? 'Estadio',
                    'city' => $matchData['ground'] ?? 'Sede',
                    'country' => 'Norteamérica 2026',
                    'starts_at' => $parsedDateTime,
                    'status' => 'scheduled',
                ]);
            }
        }

        $totalGroups = TournamentGroup::where('tournament_id', $tournament->id)->count();
        $totalTeams = Team::where('tournament_id', $tournament->id)->count();
        $totalMatches = GameMatch::where('tournament_id', $tournament->id)->count();

        $this->info("¡Carga exitosa! Se cargaron los datos oficiales del Mundial FIFA 2026.");
        $this->info("- Grupos creados: {$totalGroups}");
        $this->info("- Selecciones creadas: {$totalTeams}");
        $this->info("- Partidos creados: {$totalMatches}");
        $this->info("- Quiniela General creada: '{$pool->name}'");

        return Command::SUCCESS;
    }
}
