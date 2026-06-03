@extends('layouts.app')

@section('title', 'Calendario y Resultados')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="glass-card p-6 rounded-3xl border border-red-500/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-red-500 hover:bg-red-500/10 hover:border-red-500/20 transition-colors mr-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Calendario Oficial</span>
                <h1 class="text-2xl font-extrabold text-white">Partidos y Resultados Mundial 2026</h1>
            </div>
        </div>
    </div>

    <!-- Match Schedule List -->
    <div class="space-y-6">
        @php
            // Group matches by stage for perfect visual categorization
            $groupedMatches = $matches->groupBy('stage');
            
            $stageNames = [
                'group_stage' => 'Fase de Grupos',
                'round_of_32' => 'Dieciseisavos de Final',
                'round_of_16' => 'Octavos de Final',
                'quarter_final' => 'Cuartos de Final',
                'semi_final' => 'Semifinales',
                'third_place' => 'Tercer Lugar',
                'final' => 'Gran Final',
            ];
        @endphp

        @foreach($stageNames as $stageKey => $stageTitle)
            @if(isset($groupedMatches[$stageKey]))
                <div class="space-y-3">
                    <h2 class="text-xs font-bold text-red-400/90 uppercase tracking-widest px-1">
                        {{ $stageTitle }} ({{ $groupedMatches[$stageKey]->count() }} Partidos)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($groupedMatches[$stageKey] as $match)
                            @php
                                $isFinished = $match->status === 'finished';
                                $isLocked = $match->isPredictionsLocked();
                            @endphp
                            
                            <!-- Small Match Row Card -->
                            <div class="glass-card rounded-2xl border border-red-500/10 p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
                                
                                <!-- Card metadata -->
                                <div class="flex justify-between items-center text-[10px] text-slate-400 font-semibold border-b border-slate-800 pb-2 mb-3">
                                    <span>P. #{{ $match->match_number }} &middot; {{ $match->starts_at->format('d/m H:i') }}</span>
                                    
                                    @if($isFinished)
                                        <span class="px-2 py-0.5 rounded-full bg-slate-900 text-slate-400 border border-slate-850 font-bold uppercase tracking-wider">Final</span>
                                    @elseif($isLocked)
                                        <span class="px-2 py-0.5 rounded-full bg-red-950/40 text-red-400 border border-red-900/30 font-bold uppercase tracking-wider flex items-center gap-0.5">🔒 Bloqueado</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-950/40 text-emerald-400 border border-emerald-900/30 font-bold uppercase tracking-wider flex items-center gap-0.5">✍️ Abierto</span>
                                    @endif
                                </div>

                                <!-- Teams match lineup -->
                                <div class="flex items-center justify-between gap-4 py-1.5">
                                    <!-- Home -->
                                    <div class="flex items-center gap-2 flex-1 min-w-0">
                                        @if($match->homeTeam)
                                            <img src="{{ $match->homeTeam->flag_url }}" alt="" class="w-6 h-4 object-cover rounded-sm border border-slate-800 shrink-0">
                                            <span class="text-xs font-bold text-white truncate" title="{{ $match->homeTeam->name }}">{{ $match->homeTeam->name }}</span>
                                        @else
                                            <span class="text-xs text-slate-500 italic truncate">{{ $match->home_placeholder ?: 'Por clasificar' }}</span>
                                        @endif
                                    </div>

                                    <!-- Score display -->
                                    <div class="shrink-0 text-center px-2 py-1 rounded-xl bg-slate-950/60 border border-slate-800 min-w-[50px]">
                                        @if($isFinished)
                                            <span class="text-xs font-extrabold text-white">
                                                {{ $match->home_score }} - {{ $match->away_score }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">VS</span>
                                        @endif
                                    </div>

                                    <!-- Away -->
                                    <div class="flex items-center gap-2 flex-1 min-w-0 justify-end text-right">
                                        @if($match->awayTeam)
                                            <span class="text-xs font-bold text-white truncate" title="{{ $match->awayTeam->name }}">{{ $match->awayTeam->name }}</span>
                                            <img src="{{ $match->awayTeam->flag_url }}" alt="" class="w-6 h-4 object-cover rounded-sm border border-slate-800 shrink-0">
                                        @else
                                            <span class="text-xs text-slate-500 italic truncate">{{ $match->away_placeholder ?: 'Por clasificar' }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Display user predictions for this match if they exist -->
                                <div class="mt-3 pt-2.5 border-t border-slate-800 space-y-1">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider block">Tus Pronósticos:</span>
                                    
                                    @php
                                        $matchPredsCount = 0;
                                    @endphp
                                    @foreach($predictions as $poolId => $predsGroup)
                                        @php
                                            $pred = $predsGroup->firstWhere('match_id', $match->id);
                                        @endphp
                                        @if($pred)
                                            @php
                                                $matchPredsCount++;
                                                $pool = $pred->pool;
                                            @endphp
                                            <div class="flex items-center justify-between text-[11px] bg-slate-950/40 rounded-xl px-2 py-1 border border-slate-800">
                                                <span class="text-slate-400 font-medium truncate max-w-[120px]">{{ $pool->name }}:</span>
                                                <span class="font-bold text-white">
                                                    {{ $pred->predicted_home_score }} - {{ $pred->predicted_away_score }}
                                                    @if($isFinished)
                                                        <span class="ml-1 text-[9px] text-emerald-400 font-extrabold">(+{{ $pred->points_awarded }} Pts)</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if($matchPredsCount == 0)
                                        <span class="text-[10px] text-slate-500 italic block">No has ingresado pronóstico para este partido.</span>
                                    @endif
                                </div>

                             </div>
                        @empty
                            <div class="col-span-3 text-center text-slate-500 font-medium py-4">No hay partidos en esta fase.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
