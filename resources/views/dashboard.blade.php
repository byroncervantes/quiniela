@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="space-y-8">
    
    <!-- Welcome Header -->
    <div class="p-6 md:p-8 rounded-3xl mariscal-gradient text-white shadow-xl shadow-red-950/20 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <!-- Abstract light graphic backgrounds -->
        <div class="absolute right-0 top-0 w-64 h-64 bg-red-500/5 rounded-full blur-3xl pointer-events-none -mr-16 -mt-16"></div>
        <div class="absolute left-1/3 bottom-0 w-32 h-32 bg-red-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-2">
            <h1 class="text-3xl font-extrabold tracking-tight">¡Hola, {{ $user->name }}!</h1>
            <p class="text-red-100 max-w-xl text-sm font-medium leading-relaxed">
                Te damos la bienvenida a <strong>La Quiniela de Todos</strong>, la quiniela oficial de Distribuidora Mariscal. Prepárate para predecir los marcadores del Mundial FIFA 2026 y competir con tus compañeros de trabajo.
            </p>
            <div class="flex flex-wrap gap-2 pt-2">
                <span class="px-3 py-1 rounded-full bg-white/10 text-xs font-semibold uppercase tracking-wider text-red-200 border border-white/5">
                    {{ $user->company }}
                </span>
                <span class="px-3 py-1 rounded-full bg-white/10 text-xs font-semibold uppercase tracking-wider text-red-200 border border-white/5">
                    {{ $user->branch?->name }}
                </span>
                <span class="px-3 py-1 rounded-full bg-white/10 text-xs font-semibold uppercase tracking-wider text-red-200 border border-white/5">
                    {{ $user->department?->name }}
                </span>
            </div>
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('matches') }}" class="px-5 py-3 rounded-xl bg-white text-red-700 font-bold text-sm text-center shadow-md hover:bg-red-50 transition-all active:scale-95">
                Calendario de Partidos
            </a>
            <a href="{{ route('rules') }}" class="px-5 py-3 rounded-xl bg-red-600 text-white font-bold text-sm text-center shadow-md hover:bg-red-500 transition-all active:scale-95">
                Reglas del Juego
            </a>
        </div>
    </div>

    <!-- Active Quinielas (Joined) -->
    <div class="space-y-4">
        <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0L4 12m8 0l8-4L12 20"/></svg>
            Mis Quinielas Activas
        </h2>

        @if($joinedParticipants->isEmpty())
            <!-- Banner invitation if not joined any -->
            <div class="p-8 rounded-3xl glass-card text-center border-dashed border-2 border-slate-200 space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto text-3xl">
                    🏆
                </div>
                <h3 class="text-lg font-bold text-slate-800">Aún no estás participando en ninguna quiniela</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto">
                    Para poder ingresar tus marcadores y acumular puntos, debes unirte a una quiniela activa de la empresa.
                </p>
                <div class="pt-2">
                    @if($availablePools->isNotEmpty())
                        <div class="flex justify-center gap-3">
                            @foreach($availablePools as $pool)
                                <form action="{{ route('pools.join', $pool->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 text-white font-bold text-sm hover:bg-blue-500 shadow-md transition-colors cursor-pointer">
                                        Unirse a {{ $pool->name }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-amber-600 font-semibold">
                            ⚠️ No hay quinielas públicas abiertas en este momento. Por favor contacta al administrador.
                        </p>
                    @endif
                </div>
            </div>
        @else
            <!-- Grid of active pools -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($joinedParticipants as $participant)
                    @php 
                        $pool = $participant->pool;
                        $statusClass = [
                            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                            'blocked' => 'bg-slate-100 text-slate-800 border-slate-200',
                        ][$participant->status] ?? 'bg-slate-100 text-slate-800';

                        $statusText = [
                            'approved' => 'Aprobado y Activo',
                            'pending' => 'Pendiente de Aprobación',
                            'rejected' => 'Inscripción Rechazada',
                            'blocked' => 'Participante Bloqueado',
                        ][$participant->status] ?? $participant->status;
                    @endphp

                    <div class="glass-card rounded-3xl border border-slate-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                        <div class="p-6 space-y-4">
                            <!-- Pool Title & Status -->
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-white">{{ $pool->name }}</h3>
                                    <p class="text-xs text-red-400 font-semibold uppercase tracking-wider">{{ $pool->tournament->name }}</p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>

                            <p class="text-sm text-slate-400 leading-normal line-clamp-2">
                                {{ $pool->description ?: 'Sin descripción detallada.' }}
                            </p>

                            @if($participant->status === 'approved')
                                <!-- Statistics overview in active pool -->
                                <div class="grid grid-cols-4 gap-2 pt-2 text-center">
                                    <div class="p-2.5 rounded-2xl bg-red-950/40 border border-red-500/20">
                                        <span class="block text-2xl font-extrabold text-red-500">{{ $participant->total_points }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Puntos</span>
                                    </div>
                                    <div class="p-2.5 rounded-2xl bg-amber-950/40 border border-amber-500/20">
                                        <span class="block text-2xl font-extrabold text-amber-500">{{ $participant->exact_scores_count }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Exacto</span>
                                    </div>
                                    <div class="p-2.5 rounded-2xl bg-emerald-950/40 border border-emerald-500/20">
                                        <span class="block text-2xl font-extrabold text-emerald-500">{{ $participant->correct_results_count }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Aciertos</span>
                                    </div>
                                    <div class="p-2.5 rounded-2xl bg-slate-900/50 border border-slate-700/60">
                                        <span class="block text-2xl font-extrabold text-slate-300">
                                            #{{ $participant->current_rank ?: '-' }}
                                        </span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Puesto</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Footer Actions -->
                        <div class="px-6 py-4 bg-slate-900/50 border-t border-slate-800/80 flex items-center justify-between gap-4">
                            @if($participant->status === 'approved')
                                <a href="{{ route('pools.predictions', $pool->slug) }}" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs text-center shadow-sm transition-all cursor-pointer">
                                    Pronosticar Partidos
                                </a>
                                <a href="{{ route('pools.ranking', $pool->slug) }}" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs text-center transition-all cursor-pointer">
                                    Tabla de Posiciones
                                </a>
                            @else
                                <div class="w-full text-center text-xs text-amber-400 font-semibold bg-amber-500/10 border border-amber-500/20 rounded-xl py-2 px-3">
                                    ⌛ Tu registro en esta quiniela está pendiente de aprobación de la oficina.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Available Pools to Join -->
    @if($availablePools->isNotEmpty())
        <div class="space-y-4 pt-4">
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Otras Quinielas Disponibles
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($availablePools as $pool)
                    <div class="glass-card rounded-3xl border border-slate-800/80 overflow-hidden shadow-sm flex flex-col justify-between">
                        <div class="p-6 space-y-3">
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $pool->name }}</h3>
                                <p class="text-xs text-red-400 font-semibold uppercase tracking-wider">{{ $pool->tournament->name }}</p>
                            </div>
                            <p class="text-sm text-slate-400 leading-normal">
                                {{ $pool->description ?: 'Únete a esta quiniela para predecir marcadores.' }}
                            </p>
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                                <span>Modo de ingreso:</span>
                                <span class="text-red-500 capitalize">
                                    {{ $pool->join_mode == 'open' ? 'Abierto' : ($pool->join_mode == 'approval_required' ? 'Requiere Aprobación' : 'Código de Invitación') }}
                                </span>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-900/50 border-t border-slate-800/80">
                            @if($pool->join_mode === 'invitation_code')
                                <form action="{{ route('pools.join', $pool->id) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="invitation_code" required placeholder="Código..." 
                                        class="flex-grow px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-red-500 text-white">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-500 cursor-pointer">
                                        Unirse
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('pools.join', $pool->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs text-center transition-all cursor-pointer">
                                        Solicitar Unirse a la Quiniela
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
