<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'La Quiniela de Todos') - Quiniela Oficial Distribuidora Mariscal</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind v4 Compiler Hook -->
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #060608;
            background-image: radial-gradient(circle at center, #1b0606 0%, #060608 100%);
            color: #f1f5f9;
            -webkit-tap-highlight-color: transparent;
        }

        /* Glassmorphism details */
        .glass-card {
            background: rgba(13, 13, 16, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.6);
        }

        .mariscal-gradient {
            background: linear-gradient(135deg, #000000 0%, #991b1b 100%);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .mariscal-glow {
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.15);
        }

        /* Nav hover animation */
        .nav-link-anim {
            position: relative;
        }
        .nav-link-anim::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: #ef4444;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-link-anim:hover::after {
            width: 100%;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full flex flex-col antialiased">

    <!-- Top Premium Desktop Navbar -->
    <header class="sticky top-0 z-40 w-full glass-card border-b border-red-500/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Branding logo/name -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/la_quiniela_de_todos.jpg') }}" alt="Logo" class="w-10 h-10 rounded-xl object-cover shadow-md transform hover:rotate-12 transition-transform duration-300">
                    <div>
                        <span class="text-base font-extrabold tracking-tight text-white uppercase">La Quiniela <span class="text-red-500">de Todos</span></span>
                        <span class="hidden sm:block text-[9px] text-slate-400 font-bold tracking-wide -mt-1 uppercase">Distribuidora Mariscal</span>
                    </div>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-300">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 hover:text-white nav-link-anim transition-colors {{ Request::routeIs('dashboard') ? 'text-red-500 font-bold' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('matches') }}" class="flex items-center gap-1.5 hover:text-white nav-link-anim transition-colors {{ Request::routeIs('matches') ? 'text-red-500 font-bold' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Partidos
                    </a>
                    <a href="{{ route('rules') }}" class="flex items-center gap-1.5 hover:text-white nav-link-anim transition-colors {{ Request::routeIs('rules') ? 'text-red-500 font-bold' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Reglas
                    </a>
                    <a href="{{ route('profile') }}" class="flex items-center gap-1.5 hover:text-white nav-link-anim transition-colors {{ Request::routeIs('profile') ? 'text-red-500 font-bold' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mi Perfil
                    </a>
                    @if(in_array(Auth::user()->role, ['super_admin', 'admin_quiniela', 'moderador']))
                        <a href="/admin" class="px-3.5 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-500 transition-colors shadow-md text-xs font-bold uppercase tracking-wide border border-red-500/30">
                            Administración
                        </a>
                    @endif
                </nav>>

                <!-- Right profile controls / Logout -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-xs font-bold text-slate-200">{{ Auth::user()->name }}</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider capitalize">{{ Auth::user()->branch?->name }} &middot; {{ Auth::user()->department?->name }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-100 transition-all cursor-pointer" title="Cerrar Sesión">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-start gap-3 shadow-md animate-fade-in">
                    <svg class="w-5 h-5 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-semibold">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-start gap-3 shadow-md">
                    <svg class="w-5 h-5 text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <p class="text-sm font-semibold">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-start gap-3 shadow-md animate-shake">
                    <svg class="w-5 h-5 text-rose-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-semibold">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Mobile Navigation Tab Bar (iOS / Native style) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-black/90 backdrop-blur-md border-t border-red-500/20 py-2 shadow-[0_-4px_20px_0_rgba(0,0,0,0.8)] px-4 flex items-center justify-around">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 text-slate-500 active:scale-95 transition-all {{ Request::routeIs('dashboard') ? 'text-red-500 font-bold' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-[10px]">Inicio</span>
        </a>
        <a href="{{ route('matches') }}" class="flex flex-col items-center gap-0.5 text-slate-500 active:scale-95 transition-all {{ Request::routeIs('matches') ? 'text-red-500 font-bold' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px]">Partidos</span>
        </a>
        <a href="{{ route('rules') }}" class="flex flex-col items-center gap-0.5 text-slate-500 active:scale-95 transition-all {{ Request::routeIs('rules') ? 'text-red-500 font-bold' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span class="text-[10px]">Reglas</span>
        </a>
        <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 text-slate-500 active:scale-95 transition-all {{ Request::routeIs('profile') ? 'text-red-500 font-bold' : '' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-[10px]">Perfil</span>
        </a>
        @if(in_array(Auth::user()->role, ['super_admin', 'admin_quiniela', 'moderador']))
            <a href="/admin" class="flex flex-col items-center gap-0.5 text-red-500 font-bold active:scale-95 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span class="text-[10px]">Admin</span>
            </a>
        @endif
    </div>

    <!-- Premium Footer -->
    <footer class="bg-black/85 border-t border-red-500/10 py-6 mt-auto hidden md:block">
        <div class="max-w-7xl mx-auto px-4 text-center text-slate-500 text-xs font-semibold uppercase tracking-wider">
            &copy; {{ date('Y') }} Distribuidora Mariscal &middot; La Quiniela de Todos. Todos los derechos reservados.
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
