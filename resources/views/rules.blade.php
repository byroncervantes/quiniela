@extends('layouts.app')

@section('title', 'Reglas del Juego')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <!-- Header -->
    <div class="glass-card p-6 rounded-3xl border border-slate-100 flex items-center gap-2">
        <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Reglamento Oficial</span>
            <h1 class="text-2xl font-extrabold text-[#0f2240]">¿Cómo jugar y sumar puntos?</h1>
        </div>
    </div>

    <!-- Scoring Rules Breakdown Cards -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-[#0f2240] px-1">1. Reglas de Puntuación</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($rules as $rule)
                @php
                    $emoji = [
                        'exact_score' => '🎯',
                        'correct_winner' => '🚀',
                        'correct_draw' => '🤝',
                        'knockout_winner' => '⚽',
                    ][$rule->key] ?? '🏆';
                @endphp
                <div class="glass-card rounded-2xl border border-slate-100 p-5 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                        {{ $emoji }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-800">{{ $rule->name }}</h3>
                            <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white font-extrabold text-[10px]">
                                +{{ $rule->points }} {{ $rule->points == 1 ? 'Punto' : 'Puntos' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-normal">
                            {{ $rule->description }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tie breaker logic -->
    <div class="glass-card rounded-3xl border border-slate-100 p-6 space-y-4">
        <h2 class="text-lg font-bold text-[#0f2240] flex items-center gap-2 border-b border-slate-100 pb-3">
            <span>🏆</span> Criterios Oficiales de Desempate
        </h2>
        <p class="text-xs text-slate-500 leading-normal">
            En caso de que dos o más colaboradores de Distribuidora Mariscal finalicen empatados con la misma cantidad de puntos totales en el ranking de la quiniela, las posiciones oficiales se decidirán estrictamente siguiendo el siguiente orden lógico de desempates:
        </p>

        <ol class="space-y-3.5 pl-4 text-xs text-slate-600 leading-relaxed list-decimal">
            <li>
                <strong>Mayor total de puntos acumulados:</strong> El participante con el mayor número de puntos encabeza la tabla.
            </li>
            <li>
                <strong>Mayor número de marcadores exactos:</strong> Si persiste el empate, se premia a quien haya acertado la mayor cantidad de marcadores de partidos con goles idénticos locales y visitantes.
            </li>
            <li>
                <strong>Mayor número de aciertos de ganador o empate:</strong> Se premia a quien tenga el mayor número de aciertos simples de resultado sin marcador exacto.
            </li>
            <li>
                <strong>Menor cantidad de pronósticos fallados:</strong> Quien tenga menos predicciones equivocadas (de partidos disputados) obtiene prioridad en el desempate.
            </li>
            <li>
                <strong>Antigüedad de inscripción:</strong> Como último criterio objetivo, se favorece al colaborador que se haya inscrito primero en la quiniela del sistema.
            </li>
        </ol>
    </div>

    <!-- Company Policies -->
    <div class="glass-card rounded-3xl border border-slate-100 p-6 space-y-4">
        <h2 class="text-lg font-bold text-[#0f2240] flex items-center gap-2 border-b border-slate-100 pb-3">
            <span>🏢</span> Lineamientos y Políticas de Distribuidora Mariscal
        </h2>
        <div class="text-xs text-slate-600 space-y-4 leading-relaxed">
            <p>
                {!! nl2br(e($termsText)) !!}
            </p>
            
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                <span class="block font-bold text-slate-700">📢 Aspectos Clave a Recordar:</span>
                <ul class="list-disc pl-4 space-y-1.5 text-slate-500">
                    <li>El cierre de pronósticos de cada partido ocurre exactamente al segundo de iniciar el partido programado según la hora local de los servidores. ¡No dejes tus marcadores para última hora!</li>
                    <li>Cada colaborador tiene derecho a una única cuenta. Los perfiles duplicados o fraudulentos serán dados de baja de inmediato sin posibilidad de reclamo.</li>
                    <li>Ante cualquier discrepancia en la interpretación de los marcadores o resultados oficiales, la decisión del Administrador de la Quiniela de Distribuidora Mariscal será de carácter final.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
