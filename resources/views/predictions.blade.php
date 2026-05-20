@extends('layouts.app')

@section('title', 'Pronósticos - ' . $pool->name)

@section('styles')
<style>
    .stage-tab.active {
        background-color: #1e3a8a;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.25);
    }
    
    .match-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Top Pool Header Info -->
    <div class="glass-card p-6 rounded-3xl border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors mr-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Quiniela Activa</span>
                    <h1 class="text-2xl font-extrabold text-[#0f2240]">{{ $pool->name }}</h1>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-2 max-w-2xl leading-normal">
                Ingresa tus marcadores pronosticados. Los cambios se guardan **automáticamente** al escribir en los marcadores. Las predicciones cierran al inicio de cada partido.
            </p>
        </div>

        <!-- Participant stats -->
        <div class="flex gap-4 self-start md:self-auto">
            <div class="bg-blue-50/50 border border-blue-100 rounded-2xl px-4 py-2.5 text-center">
                <span class="block text-2xl font-extrabold text-blue-600">{{ $participant->total_points }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Puntos</span>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-2xl px-4 py-2.5 text-center">
                <span class="block text-2xl font-extrabold text-slate-700">#{{ $participant->current_rank ?: '-' }}</span>
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
                        class="stage-tab px-4 py-2 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 active:scale-95 transition-all cursor-pointer">
                        {{ $stageName }}
                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[10px]">{{ $stageMatchCount }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Active Stage Match Predictor Area -->
    <form id="predictions-bulk-form" action="{{ route('pools.predictions.bulk-save', $pool->slug) }}" method="POST">
        @csrf
        
        @foreach($stages as $stageKey => $stageName)
            @php
                $stageMatches = $matches->where('stage', $stageKey);
            @endphp
            @if($stageMatches->count() > 0)
                <div id="stage-panel-{{ $stageKey }}" class="stage-panel space-y-4" style="display: none;">
                    
                    <div class="flex justify-between items-center px-1">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">{{ $stageName }}</h3>
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
                            <div class="match-card glass-card rounded-3xl border border-slate-100 p-5 flex flex-col justify-between relative overflow-hidden {{ $isLocked ? 'bg-slate-50/50' : '' }}" id="match-container-{{ $match->id }}">
                                
                                <!-- Card Header: Match Details & Badges -->
                                <div class="flex justify-between items-center border-b border-slate-100/80 pb-3 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-500 font-bold text-[10px] flex items-center justify-center">
                                            #{{ $match->match_number }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-semibold">
                                            {{ $match->starts_at->format('d/m/Y H:i') }} - {{ $match->stadium }}, {{ $match->city }}
                                        </span>
                                    </div>

                                    @if($match->status === 'finished')
                                        <span class="px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-800 border border-slate-300 text-[10px] font-bold">
                                            Finalizado
                                        </span>
                                    @elseif($isLocked)
                                        <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-200 text-[10px] font-bold flex items-center gap-1">
                                            🔒 Cerrado
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold flex items-center gap-1">
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
                                                class="w-12 h-7.5 rounded-md object-cover shadow-sm border border-slate-100">
                                            <span class="text-xs font-bold text-slate-800 leading-tight">{{ $match->homeTeam->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $match->homeTeam->fifa_code }}</span>
                                        @else
                                            <div class="w-12 h-7.5 rounded-md bg-slate-100 flex items-center justify-center border border-slate-200">
                                                ❓
                                            </div>
                                            <span class="text-xs font-medium text-slate-400 italic leading-tight">{{ $match->home_placeholder ?: 'Por clasificar' }}</span>
                                        @endif
                                    </div>

                                    <!-- Middle Inputs Core -->
                                    <div class="col-span-1 flex flex-col items-center justify-center gap-1">
                                        @if($isLocked)
                                            <!-- Readonly / Frozen Predictions -->
                                            <div class="flex items-center gap-1 text-slate-800">
                                                <span class="w-8 h-8 rounded-xl bg-slate-100 font-extrabold text-sm flex items-center justify-center border border-slate-200">
                                                    {{ $homeScoreVal !== '' ? $homeScoreVal : '-' }}
                                                </span>
                                                <span class="text-slate-300 font-bold text-xs">-</span>
                                                <span class="w-8 h-8 rounded-xl bg-slate-100 font-extrabold text-sm flex items-center justify-center border border-slate-200">
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
                                                    class="w-9 h-9 rounded-xl bg-white border border-slate-300 text-center font-extrabold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                
                                                <span class="text-slate-300 font-extrabold text-xs">-</span>
                                                
                                                <input type="number" min="0" max="99" 
                                                    name="predictions[{{ $match->id }}][away]" 
                                                    id="score-away-{{ $match->id }}"
                                                    value="{{ $awayScoreVal }}"
                                                    oninput="autoSavePrediction({{ $match->id }})"
                                                    class="w-9 h-9 rounded-xl bg-white border border-slate-300 text-center font-extrabold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Away Team Flag & Name -->
                                    <div class="col-span-3 flex flex-col items-center text-center space-y-1.5">
                                        @if($match->awayTeam)
                                            <img src="{{ $match->awayTeam->flag_url ?: 'https://placehold.co/100x60/dbeafe/1e3a8a?text=' . $match->awayTeam->fifa_code }}" 
                                                alt="Bandera {{ $match->awayTeam->name }}" 
                                                class="w-12 h-7.5 rounded-md object-cover shadow-sm border border-slate-100">
                                            <span class="text-xs font-bold text-slate-800 leading-tight">{{ $match->awayTeam->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $match->awayTeam->fifa_code }}</span>
                                        @else
                                            <div class="w-12 h-7.5 rounded-md bg-slate-100 flex items-center justify-center border border-slate-200">
                                                ❓
                                            </div>
                                            <span class="text-xs font-medium text-slate-400 italic leading-tight">{{ $match->away_placeholder ?: 'Por clasificar' }}</span>
                                        @endif
                                    </div>

                                </div>

                                <!-- Knockout Extras: Penalties & Advancing Team -->
                                @if($stageKey !== 'group_stage')
                                    <div class="mt-2 pt-3 border-t border-slate-50 text-xs space-y-3" id="knockout-section-{{ $match->id }}">
                                        <div class="p-2.5 rounded-2xl bg-amber-50/50 border border-amber-100/50 text-[11px] text-amber-800 font-medium">
                                            ⚠️ Fases eliminatorias: Si pronosticas empate en los goles, debes elegir un **clasificado**.
                                        </div>

                                        @if($isLocked)
                                            @if($winnerIdVal)
                                                @php $wTeam = $match->homeTeam && $match->homeTeam->id == $winnerIdVal ? $match->homeTeam : ($match->awayTeam && $match->awayTeam->id == $winnerIdVal ? $match->awayTeam : null); @endphp
                                                <div class="flex items-center justify-between font-semibold text-slate-700">
                                                    <span>Clasifica:</span>
                                                    <span class="text-blue-600">{{ $wTeam ? $wTeam->name : 'N/A' }}</span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="flex items-center justify-between gap-4">
                                                <label class="font-bold text-slate-600 shrink-0">Quién Clasifica:</label>
                                                <select name="predictions[{{ $match->id }}][predicted_winner]" 
                                                    id="winner-team-{{ $match->id }}"
                                                    onchange="autoSavePrediction({{ $match->id }})"
                                                    class="flex-grow max-w-[180px] px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
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
                                    <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div class="text-xs">
                                            <span class="text-slate-400 font-semibold block">Marcador Real:</span>
                                            <span class="text-slate-700 font-extrabold text-sm">
                                                {{ $match->home_score }} - {{ $match->away_score }}
                                                @if($match->home_penalty_score !== null || $match->away_penalty_score !== null)
                                                    <span class="text-[10px] text-slate-400 font-normal">({{ $match->home_penalty_score }} - {{ $match->away_penalty_score }} pen)</span>
                                                @endif
                                            </span>
                                        </div>

                                        <div class="text-right">
                                            @php
                                                $awardedPoints = $pred ? $pred->points_awarded : 0;
                                                $detail = $pred ? $pred->scoring_detail : null;
                                                $badgeClass = $awardedPoints == 3 ? 'bg-emerald-500 text-white shadow-emerald-100' : ($awardedPoints > 0 ? 'bg-blue-600 text-white shadow-blue-100' : 'bg-slate-200 text-slate-600');
                                                
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
                                            <span class="text-[10px] font-bold block text-slate-400 mb-0.5">{{ $desc }}</span>
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
            <a href="{{ route('dashboard') }}" class="px-5 py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-700 font-bold text-sm text-center shadow-sm hover:bg-slate-50 transition-all active:scale-98">
                Volver al Panel
            </a>
            <button type="submit" class="px-6 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm text-center shadow-md transition-all active:scale-98 cursor-pointer">
                Guardar Todos los Marcadores
            </button>
        </div>
    </form>
</div>

<!-- Floating dynamic auto-save status indicator at bottom right -->
<div id="autosave-toast" class="fixed bottom-20 md:bottom-8 right-6 z-50 glass-card px-4 py-3 rounded-2xl border border-slate-200 shadow-xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 ease-out">
    <div class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center animate-ping text-white text-[8px] font-black"></div>
    <span class="text-xs font-semibold text-slate-700" id="autosave-toast-text">Pronósticos guardados.</span>
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
