@extends('layouts.app')

@section('title', 'Reglas del Juego')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    
    <!-- Header -->
    <div class="glass-card p-6 rounded-3xl border border-red-500/10 flex items-center gap-2">
        <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-red-500 hover:bg-red-500/10 hover:border-red-500/20 transition-colors mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Reglamento Oficial</span>
            <h1 class="text-2xl font-extrabold text-white">¿Cómo jugar y sumar puntos?</h1>
        </div>
    </div>

    <!-- Scoring Rules Breakdown Cards -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-white px-1">1. Reglas de Puntuación</h2>
        
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
                <div class="glass-card rounded-2xl border border-red-500/10 p-5 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center text-2xl shrink-0 border border-red-500/20">
                        {{ $emoji }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-white">{{ $rule->name }}</h3>
                            <span class="px-2 py-0.5 rounded-full bg-red-600 text-white font-extrabold text-[10px] uppercase tracking-wider">
                                +{{ $rule->points }} {{ $rule->points == 1 ? 'Punto' : 'Puntos' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">
                            {{ $rule->description }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tie breaker logic -->
    <div class="glass-card rounded-3xl border border-red-500/10 p-6 space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
            <span class="text-red-500">🏆</span> Criterios Oficiales de Desempate
        </h2>
        <p class="text-xs text-slate-400 leading-normal">
            En caso de que dos o más colaboradores de Distribuidora Mariscal finalicen empatados con la misma cantidad de puntos totales en el ranking de la quiniela, las posiciones oficiales se decidirán estrictamente siguiendo el siguiente orden lógico de desempates:
        </p>

        <ol class="space-y-3.5 pl-4 text-xs text-slate-300 leading-relaxed list-decimal">
            <li>
                <strong class="text-white">Mayor total de puntos acumulados:</strong> El participante con el mayor número de puntos encabeza la tabla.
            </li>
            <li>
                <strong class="text-white">Mayor número de marcadores exactos:</strong> Si persiste el empate, se premia a quien haya acertado la mayor cantidad de marcadores de partidos con goles idénticos locales y visitantes.
            </li>
            <li>
                <strong class="text-white">Mayor número de aciertos de ganador o empate:</strong> Se premia a quien tenga el mayor número de aciertos simples de resultado sin marcador exacto.
            </li>
            <li>
                <strong class="text-white">Menor cantidad de pronósticos fallados:</strong> Quien tenga menos predicciones equivocadas (de partidos disputados) obtiene prioridad en el desempate.
            </li>
            <li>
                <strong class="text-white">Antigüedad de inscripción:</strong> Como último criterio objetivo, se favorece al colaborador que se haya inscrito primero en la quiniela del sistema.
            </li>
        </ol>
    </div>

    <!-- Company Policies -->
    <div class="glass-card rounded-3xl border border-red-500/10 p-6 space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
            <span class="text-red-500">🏢</span> Lineamientos y Políticas de Distribuidora Mariscal
        </h2>
        <div class="text-xs text-slate-300 space-y-4 leading-relaxed">
            <p>
                {!! nl2br(e($termsText)) !!}
            </p>
            
            <div class="p-4 rounded-2xl bg-slate-950/40 border border-red-500/10 space-y-2">
                <span class="block font-bold text-slate-200">📢 Aspectos Clave a Recordar:</span>
                <ul class="list-disc pl-4 space-y-1.5 text-slate-400">
                    <li>El cierre de pronósticos de cada partido irá ocurriendo de acuerdo a las indicaciones que se irán dando en los chats de comunicación oficial ¡No dejes tus marcadores para último hora!</li>
                    <li>Cada colaborador tiene derecho a una única cuenta. Los perfiles duplicados o fraudulentos serán dados de baja de inmediato sin posibilidad de reclamo.</li>
                    <li>Ante cualquier discrepancia en la interpretación de los marcadores o resultados oficiales, la decisión del Administrador de la Quiniela de Distribuidora Mariscal será de carácter final.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
