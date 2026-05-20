@extends('layouts.app')

@section('title', 'Ranking - ' . $pool->name)

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="glass-card p-6 rounded-3xl border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors mr-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Líderes de la Quiniela</span>
                <h1 class="text-2xl font-extrabold text-[#0f2240]">{{ $pool->name }}</h1>
            </div>
        </div>
        
        <span class="px-3.5 py-1.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-bold uppercase tracking-wider self-start sm:self-auto">
            Actualizado en Tiempo Real
        </span>
    </div>

    <!-- Leaderboard Table Container -->
    <div class="glass-card rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
        
        <!-- Search bar/Filters -->
        <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex flex-col sm:flex-row items-center gap-3 justify-between">
            <div class="relative w-full sm:max-w-xs">
                <input type="text" id="ranking-search" oninput="filterRankingTable()" placeholder="Buscar colaborador..." 
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <div class="absolute left-3 top-2.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Tie-breaker criteria helper -->
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider hidden lg:block">
                Criterios: Puntos > Goles Exactos > Aciertos Ganador > Menos Fallados > Fecha de Unión
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left" id="ranking-table">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-16">Puesto</th>
                        <th class="py-3.5 px-4 w-12"></th> <!-- Rank diff arrow -->
                        <th class="py-3.5 px-4">Colaborador</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Sucursal</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Departamento</th>
                        <th class="py-3.5 px-4 text-center">Pts</th>
                        <th class="py-3.5 px-4 text-center hidden sm:table-cell">Marcador Exacto</th>
                        <th class="py-3.5 px-4 text-center hidden sm:table-cell">Acierto Ganador</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($participants as $index => $part)
                        @php
                            $user = $part->user;
                            $rank = $part->current_rank ?: ($index + 1);
                            $prevRank = $part->previous_rank ?: $rank;
                            
                            // Visual medals for Top 3
                            $medal = '';
                            if ($rank == 1) $medal = '🥇';
                            elseif ($rank == 2) $medal = '🥈';
                            elseif ($rank == 3) $medal = '🥉';

                            // Rank Diff Shift Arrow indicators
                            $shiftArrow = '';
                            $shiftClass = '';
                            if ($rank < $prevRank) {
                                // Climbed ranks
                                $shiftArrow = '▲ ' . ($prevRank - $rank);
                                $shiftClass = 'text-emerald-500 bg-emerald-50';
                            } elseif ($rank > $prevRank) {
                                // Fell down
                                $shiftArrow = '▼ ' . ($rank - $prevRank);
                                $shiftClass = 'text-rose-500 bg-rose-50';
                            } else {
                                // Stayed same
                                $shiftArrow = '●';
                                $shiftClass = 'text-slate-300';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors ranking-row" data-name="{{ strtolower($user->name) }}">
                            
                            <!-- Position Rank -->
                            <td class="py-4 px-4 text-center font-bold text-slate-800">
                                @if($medal)
                                    <span class="text-xl inline-block" title="Puesto {{ $rank }}">{{ $medal }}</span>
                                @else
                                    <span class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-xs text-slate-500 border border-slate-200/50">
                                        {{ $rank }}
                                    </span>
                                @endif
                            </td>

                            <!-- Rank Diff Arrow -->
                            <td class="py-4 px-4 text-center font-extrabold text-[10px]">
                                <span class="px-2 py-0.5 rounded-full {{ $shiftClass }}" title="Posición previa: {{ $prevRank }}">
                                    {!! $shiftArrow !!}
                                </span>
                            </td>

                            <!-- Employee Profile -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <!-- Initials avatar -->
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-700 to-blue-900 text-white font-extrabold text-xs flex items-center justify-center shadow-inner">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="block font-bold text-slate-700 leading-tight">{{ $user->name }}</span>
                                        <span class="block md:hidden text-[10px] text-slate-400 font-medium mt-0.5">{{ $user->branch }} &middot; {{ $user->department }}</span>
                                        <span class="hidden md:block text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Cód. {{ $user->employee_code }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Branch Desktop -->
                            <td class="py-4 px-4 text-slate-600 font-medium hidden md:table-cell">
                                {{ $user->branch }}
                            </td>

                            <!-- Department Desktop -->
                            <td class="py-4 px-4 text-slate-600 font-medium hidden md:table-cell">
                                {{ $user->department }}
                            </td>

                            <!-- Points -->
                            <td class="py-4 px-4 text-center">
                                <span class="px-3 py-1.5 rounded-xl bg-blue-600 font-extrabold text-xs text-white shadow-sm shadow-blue-500/10">
                                    {{ $part->total_points }} Pts
                                </span>
                            </td>

                            <!-- Exact Count -->
                            <td class="py-4 px-4 text-center text-xs font-bold text-slate-700 hidden sm:table-cell">
                                <span class="px-2 py-1 rounded-lg bg-amber-50 border border-amber-100 text-amber-700">
                                    {{ $part->exact_scores_count }}
                                </span>
                            </td>

                            <!-- Winner Count -->
                            <td class="py-4 px-4 text-center text-xs font-bold text-slate-700 hidden sm:table-cell">
                                <span class="px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700">
                                    {{ $part->correct_results_count }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 font-medium italic">
                                Aún no hay ningún participante aprobado en esta quiniela.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Search table filter logic
    function filterRankingTable() {
        const searchVal = document.getElementById('ranking-search').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.ranking-row');

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            if (name.includes(searchVal)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
