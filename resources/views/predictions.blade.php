@extends('layouts.app')

@section('title', 'Pronósticos - ' . $pool->name)

@section('styles')
<style>
    .stage-tab.active {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
    }
    
    .match-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Top Pool Header Info -->
    <div class="glass-card p-6 rounded-3xl border border-red-500/10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-red-500 hover:bg-red-500/10 hover:border-red-500/20 transition-colors mr-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Quiniela Activa</span>
                    <h1 class="text-2xl font-extrabold text-white">{{ $pool->name }}</h1>
                </div>
            </div>
            <p class="text-sm text-slate-400 mt-2 max-w-2xl leading-normal">
                Ingresa tus marcadores pronosticados. Los cambios se guardan **automáticamente** al escribir en los marcadores. Las predicciones cierran al inicio de cada partido.
            </p>
        </div>

        <!-- Participant stats -->
        <div class="flex gap-4 self-start md:self-auto">
            <div class="bg-red-500/10 border border-red-500/20 rounded-2xl px-4 py-2.5 text-center">
                <span class="block text-2xl font-extrabold text-red-500">{{ $participant->total_points }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Puntos</span>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl px-4 py-2.5 text-center">
                <span class="block text-2xl font-extrabold text-slate-200">#{{ $participant->current_rank ?: '-' }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Ranking</span>
            </div>
        </div>
    </div>

    <!-- Responsive Stage Navigation Tabs -->
    <div class="overflow-x-auto pb-2 -mx-4 px-4 scrollbar-none">
        <div class="flex space-x-2 min-w-max">
            @foreach($stages as $stageKey => $stageName)
                @php
                    $stageMatchCount = $matches->where('stage', $stageKey)->count();
                @endphp
                @if($stageMatchCount > 0)
                    <button type="button" onclick="switchStage('{{ $stageKey }}')" id="tab-{{ $stageKey }}"
                        class="stage-tab px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 border border-slate-850 text-slate-400 hover:bg-slate-850 hover:text-white active:scale-95 transition-all cursor-pointer">
                        {{ $stageName }}
                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-slate-950 text-slate-500 text-[10px]">{{ $stageMatchCount }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>    <!-- Active Stage Match Predictor Area -->
    <form id="predictions-bulk-form" action="{{ route('pools.predictions.bulk-save', $pool->slug) }}" method="POST">
        @csrf
        
        @foreach($stages as $stageKey => $stageName)
            @php
                $stageMatches = $matches->where('stage', $stageKey);
            @endphp
            @if($stageMatches->count() > 0)
                <div id="stage-panel-{{ $stageKey }}" class="stage-panel space-y-4" style="display: none;">
                    
                    <div class="flex justify-between items-center px-1">
                        <h3 class="text-sm font-bold text-red-400/90 uppercase tracking-wider">{{ $stageName }}</h3>
                        <span class="text-xs text-slate-400 font-semibold">Total Partidos: {{ $stageMatches->count() }}</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        @foreach($stageMatches as $match)
                            @php
                                $pred = $predictions->get($match->id);
                                $isLocked = $match->isPredictionsLocked();
                                
                                // Score check values
                                $homeScoreVal = $pred ? $pred->predicted_home_score : '';
                                $awayScoreVal = $pred ? $pred->predicted_away_score : '';
                                $homePenVal = $pred ? $pred->predicted_home_penalty_score : '';
                                $awayPenVal = $pred ? $pred->predicted_away_penalty_score : '';
                                $winnerIdVal = $pred ? $pred->predicted_winner_team_id : '';
                            @endphp

                            <!-- Individual Match Card -->
                            <div class="match-card glass-card rounded-3xl border border-red-500/10 p-5 flex flex-col justify-between relative overflow-hidden {{ $isLocked ? 'bg-slate-950/40 opacity-75' : '' }}" id="match-container-{{ $match->id }}">
                                
                                <!-- Card Header: Match Details & Badges -->
                                <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-slate-900 text-slate-400 border border-slate-800 font-bold text-[10px] flex items-center justify-center">
                                            #{{ $match->match_number }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-semibold">
                                            {{ $match->starts_at->format('d/m/Y H:i') }} - {{ $match->stadium }}, {{ $match->city }}
                                        </span>
                                    </div>

                                    @if($match->status === 'finished')
                                        <span class="px-2.5 py-0.5 rounded-full bg-slate-900 text-slate-400 border border-slate-800 text-[10px] font-bold">
                                            Finalizado
                                        </span>
                                    @elseif($isLocked)
                                        <span class="px-2.5 py-0.5 rounded-full bg-red-950/40 text-red-400 border border-red-900/30 text-[10px] font-bold flex items-center gap-1">
                                            🔒 Cerrado
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-950/40 text-emerald-400 border border-emerald-900/30 text-[10px] font-bold flex items-center gap-1">
                                            ✍️ Abierto
                                        </span>
                                    @endif
                                </div>

                                <!-- Card Core: Team matchup rows -->
                                <div class="grid grid-cols-7 items-center gap-2 mb-4">
                                    
                                    <!-- Home Team Flag & Name -->
                                    <div class="col-span-3 flex flex-col items-center text-center space-y-1.5">
                                        @if($match->homeTeam)
                                            <img src="{{ $match->homeTeam->flag_url ?: 'https://placehold.co/100x60/dbeafe/1e3a8a?text=' . $match->homeTeam->fifa_code }}" 
                                                alt="Bandera {{ $match->homeTeam->name }}" 
                                                class="w-12 h-7.5 rounded-md object-cover shadow-sm border border-slate-800">
                                            <span class="text-xs font-bold text-white leading-tight">{{ $match->homeTeam->name }}</span>
                                            <span class="text-[10px] text-slate-450 font-bold uppercase">{{ $match->homeTeam->fifa_code }}</span>
                                        @else
                                            <div class="w-12 h-7.5 rounded-md bg-slate-900 flex items-center justify-center border border-slate-800">
                                                ❓
                                            </div>
                                            <span class="text-xs font-medium text-slate-500 italic leading-tight">{{ $match->home_placeholder ?: 'Por clasificar' }}</span>
                                        @endif
                                    </div>

                                    <!-- Middle Inputs Core -->
                                    <div class="col-span-1 flex flex-col items-center justify-center gap-1">
                                        @if($isLocked)
                                            <!-- Readonly / Frozen Predictions -->
                                            <div class="flex items-center gap-1 text-white">
                                                <span class="w-8 h-8 rounded-xl bg-slate-950 font-extrabold text-sm flex items-center justify-center border border-slate-800">
                                                    {{ $homeScoreVal !== '' ? $homeScoreVal : '-' }}
                                                </span>
                                                <span class="text-slate-500 font-bold text-xs">-</span>
                                                <span class="w-8 h-8 rounded-xl bg-slate-950 font-extrabold text-sm flex items-center justify-center border border-slate-800">
                                                    {{ $awayScoreVal !== '' ? $awayScoreVal : '-' }}
                                                </span>
                                            </div>
                                        @else
                                            <!-- Dynamic Editable Inputs -->
                                            <div class="flex items-center gap-1.5">
                                                <input type="number" min="0" max="99" 
                                                    name="predictions[{{ $match->id }}][home]" 
                                                    id="score-home-{{ $match->id }}"
                                                    value="{{ $homeScoreVal }}"
                                                    oninput="autoSavePrediction({{ $match->id }})"
                                                    class="w-9 h-9 rounded-xl bg-slate-950 border border-slate-800 text-white text-center font-extrabold text-sm focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                
                                                <span class="text-slate-500 font-extrabold text-xs">-</span>
                                                
                                                <input type="number" min="0" max="99" 
                                                    name="predictions[{{ $match->id }}][away]" 
                                                    id="score-away-{{ $match->id }}"
                                                    value="{{ $awayScoreVal }}"
                                                    oninput="autoSavePrediction({{ $match->id }})"
                                                    class="w-9 h-9 rounded-xl bg-slate-950 border border-slate-800 text-white text-center font-extrabold text-sm focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Away Team Flag & Name -->
                                    <div class="col-span-3 flex flex-col items-center text-center space-y-1.5">
                                        @if($match->awayTeam)
                                            <img src="{{ $match->awayTeam->flag_url ?: 'https://placehold.co/100x60/dbeafe/1e3a8a?text=' . $match->awayTeam->fifa_code }}" 
                                                alt="Bandera {{ $match->awayTeam->name }}" 
                                                class="w-12 h-7.5 rounded-md object-cover shadow-sm border border-slate-800">
                                            <span class="text-xs font-bold text-white leading-tight">{{ $match->awayTeam->name }}</span>
                                            <span class="text-[10px] text-slate-450 font-bold uppercase">{{ $match->awayTeam->fifa_code }}</span>
                                        @else
                                            <div class="w-12 h-7.5 rounded-md bg-slate-900 flex items-center justify-center border border-slate-800">
                                                ❓
                                            </div>
                                            <span class="text-xs font-medium text-slate-500 italic leading-tight">{{ $match->away_placeholder ?: 'Por clasificar' }}</span>
                                        @endif
                                    </div>

                                </div>

                                <!-- Knockout Extras: Penalties & Advancing Team -->
                                @if($stageKey !== 'group_stage')
                                    <div class="mt-2 pt-3 border-t border-slate-800 text-xs space-y-3" id="knockout-section-{{ $match->id }}">
                                        <div class="p-2.5 rounded-2xl bg-amber-950/30 border border-amber-900/20 text-[11px] text-amber-400 font-medium">
                                            ⚠️ Fases eliminatorias: Si pronosticas empate en los goles, debes elegir un **clasificado**.
                                        </div>

                                        @if($isLocked)
                                            @if($winnerIdVal)
                                                @php $wTeam = $match->homeTeam && $match->homeTeam->id == $winnerIdVal ? $match->homeTeam : ($match->awayTeam && $match->awayTeam->id == $winnerIdVal ? $match->awayTeam : null); @endphp
                                                <div class="flex items-center justify-between font-semibold text-slate-300">
                                                    <span>Clasifica:</span>
                                                    <span class="text-red-500 font-bold">{{ $wTeam ? $wTeam->name : 'N/A' }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex items-center justify-between gap-4">
                                                <label class="font-bold text-slate-400 shrink-0">Quién Clasifica:</label>
                                                <select name="predictions[{{ $match->id }}][predicted_winner]" 
                                                    id="winner-team-{{ $match->id }}"
                                                    onchange="autoSavePrediction({{ $match->id }})"
                                                    class="flex-grow max-w-[180px] px-2.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:ring-1 focus:ring-red-500 select-dark">
                                                    <option value="">Seleccionar clasificado...</option>
                                                    @if($match->homeTeam)
                                                        <option value="{{ $match->homeTeam->id }}" {{ $winnerIdVal == $match->homeTeam->id ? 'selected' : '' }}>{{ $match->homeTeam->name }}</option>
                                                    @endif
                                                    @if($match->awayTeam)
                                                        <option value="{{ $match->awayTeam->id }}" {{ $winnerIdVal == $match->awayTeam->id ? 'selected' : '' }}>{{ $match->awayTeam->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Prediction Score Output (Real match comparison results) -->
                                @if($match->status === 'finished')
                                    <div class="mt-4 pt-3.5 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="text-xs">
                                            <span class="text-slate-500 font-semibold block">Marcador Real:</span>
                                            <span class="text-white font-extrabold text-sm">
                                                {{ $match->home_score }} - {{ $match->away_score }}
                                                @if($match->home_penalty_score !== null || $match->away_penalty_score !== null)
                                                    <span class="text-[10px] text-slate-500 font-normal">({{ $match->home_penalty_score }} - {{ $match->away_penalty_score }} pen)</span>
                                                @endif
                                            </span>
                                        </div>

                                        <div class="text-right">
                                            @php
                                                $awardedPoints = $pred ? $pred->points_awarded : 0;
                                                $detail = $pred ? $pred->scoring_detail : null;
                                                $badgeClass = $awardedPoints == 3 ? 'bg-emerald-600 text-white' : ($awardedPoints > 0 ? 'bg-red-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800');
                                                
                                                $desc = 'Cero puntos';
                                                if($detail) {
                                                    if($detail['exact_score'] ?? false) {
                                                        $desc = '¡Marcador Exacto! 🎯';
                                                    } elseif($detail['correct_winner'] ?? false) {
                                                        $desc = 'Ganador Correcto 🚀';
                                                    } elseif($detail['correct_draw'] ?? false) {
                                                        $desc = 'Empate Correcto 🤝';
                                                    }
                                                    if($detail['acerted_knockout_winner'] ?? false) {
                                                        $desc .= ' (+Clasificado)';
                                                    }
                                                }
                                            @endphp
                                            <span class="text-[10px] font-bold block text-slate-500 mb-0.5">{{ $desc }}</span>
                                            <span class="px-3 py-1 rounded-xl text-xs font-extrabold shadow-sm {{ $badgeClass }}">
                                                +{{ $awardedPoints }} {{ $awardedPoints == 1 ? 'Punto' : 'Puntos' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                             </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Bulk action button fallback -->
        <div class="mt-8 flex justify-end gap-3 pb-8">
            <a href="{{ route('dashboard') }}" class="px-5 py-3.5 rounded-2xl bg-slate-900 border border-slate-850 text-slate-300 hover:bg-slate-800 hover:text-white transition-all active:scale-98">
                Volver al Panel
            </a>
            <button type="submit" class="px-6 py-3.5 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm text-center shadow-md transition-all active:scale-98 cursor-pointer">
                Guardar Todos los Marcadores
            </button>
        </div>
    </form>
</div>

<!-- Floating dynamic auto-save status indicator at bottom right -->
<div id="autosave-toast" class="fixed bottom-20 md:bottom-8 right-6 z-50 glass-card px-4 py-3 rounded-2xl border border-red-500/10 shadow-xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 ease-out">
    <div class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center animate-ping text-white text-[8px] font-black"></div>
    <span class="text-xs font-semibold text-slate-200" id="autosave-toast-text">Pronósticos guardados.</span>
</div>

@endsection

@section('scripts')
<script>
    // Toggle dashboard stage panels
    let activeStageKey = '';

    function switchStage(stageKey) {
        // Hide active panel
        if (activeStageKey) {
            document.getElementById('stage-panel-' + activeStageKey).style.display = 'none';
            document.getElementById('tab-' + activeStageKey).classList.remove('active');
        }

        // Show new panel
        document.getElementById('stage-panel-' + stageKey).style.display = 'block';
        document.getElementById('tab-' + stageKey).classList.add('active');
        
        activeStageKey = stageKey;
        
        // Save state in localStorage to preserve tabs on reload
        localStorage.setItem('quinmariscal_active_stage', stageKey);
    }

    // Load initial tab from history or default
    document.addEventListener("DOMContentLoaded", function() {
        let savedStage = localStorage.getItem('quinmariscal_active_stage');
        
        // Pick first existing tab if none in cache
        if (!savedStage) {
            let firstTab = document.querySelector('.stage-tab');
            if (firstTab) {
                savedStage = firstTab.id.replace('tab-', '');
            }
        }

        if (savedStage) {
            switchStage(savedStage);
        }
    });

    // Elegant background AJAX Auto-Save prediction logic
    let autoSaveTimers = {};

    function autoSavePrediction(matchId) {
        // Clear existing debounce timer to avoid firing repeated requests
        if (autoSaveTimers[matchId]) {
            clearTimeout(autoSaveTimers[matchId]);
        }

        // Debounce for 800ms
        autoSaveTimers[matchId] = setTimeout(function() {
            sendPredictionToServer(matchId);
        }, 800);
    }

    function sendPredictionToServer(matchId) {
        const homeScoreInput = document.getElementById('score-home-' + matchId);
        const awayScoreInput = document.getElementById('score-away-' + matchId);
        const winnerSelect = document.getElementById('winner-team-' + matchId);

        if (!homeScoreInput || !awayScoreInput) return;

        const homeScoreVal = homeScoreInput.value;
        const awayScoreVal = awayScoreInput.value;
        const winnerIdVal = winnerSelect ? winnerSelect.value : null;

        // Validation check: require both numbers
        if (homeScoreVal === '' || awayScoreVal === '') {
            return; 
        }

        // Apply a visual loading glow to card border
        const container = document.getElementById('match-container-' + matchId);
        container.classList.add('ring-2', 'ring-blue-200/50');

        showToast('Guardando automáticamente... 💾', 'blue');

        // Fire background POST request
        fetch('{{ route("pools.predictions.save-ajax", $pool->slug) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                match_id: matchId,
                home_score: parseInt(homeScoreVal),
                away_score: parseInt(awayScoreVal),
                predicted_winner_team_id: winnerIdVal ? parseInt(winnerIdVal) : null
            })
        })
        .then(response => response.json())
        .then(data => {
            container.classList.remove('ring-2', 'ring-blue-200/50');
            if (data.success) {
                // Success glow animation
                container.classList.add('ring-2', 'ring-emerald-200/50');
                setTimeout(() => container.classList.remove('ring-2', 'ring-emerald-200/50'), 1500);
                showToast('¡Marcador guardado automáticamente! 💾', 'emerald');
            } else {
                // Error glow
                container.classList.add('ring-2', 'ring-red-200/50');
                setTimeout(() => container.classList.remove('ring-2', 'ring-red-200/50'), 1500);
                showToast('Error: ' + (data.error || 'No se pudo guardar.'), 'red');
            }
        })
        .catch(error => {
            container.classList.remove('ring-2', 'ring-blue-200/50');
            container.classList.add('ring-2', 'ring-red-200/50');
            setTimeout(() => container.classList.remove('ring-2', 'ring-red-200/50'), 1500);
            showToast('Error de red. Usa el botón inferior para guardar.', 'red');
        });
    }

    // Elegant bottom toast notifications
    let toastTimeout = null;

    function showToast(text, color) {
        const toast = document.getElementById('autosave-toast');
        const textSpan = document.getElementById('autosave-toast-text');
        
        textSpan.innerHTML = text;

        // Style the dot dynamically
        const dot = toast.querySelector('div');
        dot.className = `w-4 h-4 rounded-full flex items-center justify-center animate-ping text-white text-[8px] font-black bg-${color}-500`;

        // Clear existing hide timers
        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }

        // Animate up
        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        // Hide after 3 seconds
        toastTimeout = setTimeout(function() {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }
</script>
@endsection
