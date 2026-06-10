<!DOCTYPE html>
<html lang="es" class="h-full bg-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - La Quiniela de Todos</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind v4 Compiler Hook -->
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at center, #1b0606 0%, #000000 100%);
            -webkit-tap-highlight-color: transparent;
        }

        .glass-login {
            background: rgba(13, 13, 16, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(239, 68, 68, 0.15);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
        }

        .glow-input:focus {
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
            border-color: #ef4444;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo -->
        <img src="{{ asset('img/la_quiniela_de_todos.jpg') }}" alt="Logo La Quiniela de Todos" 
            style="max-width: 160px !important; width: 100% !important; height: auto !important; aspect-ratio: 1/1 !important; object-fit: contain !important;"
            class="mx-auto rounded-3xl shadow-2xl border border-red-500/20 mb-4 transform hover:scale-105 transition-transform duration-300">
        
        <h2 class="text-3xl font-black tracking-tight text-white uppercase">
            La Quiniela <span class="text-red-500">de Todos</span>
        </h2>
        <p class="mt-2 text-xs text-slate-400 font-bold uppercase tracking-widest leading-relaxed max-w-sm mx-auto px-4">
            Unión D Mayoreo &middot; Ceramipiso &middot; Distribuidora Mariscal &middot; Boulevard Design
        </p>
        <p class="mt-1 text-xs text-red-500 font-semibold tracking-wider uppercase">
            Mundial FIFA 2026
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="glass-login py-8 px-6 rounded-3xl sm:px-10">
            
            @if(session('status'))
                <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <form class="space-y-6" action="{{ route('login') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-300">
                        Correo Electrónico
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="usuario@distmariscal.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-300">
                        Contraseña
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4.5 w-4.5 rounded border-slate-700 bg-slate-900 text-red-600 focus:ring-red-500 focus:ring-offset-slate-950">
                        <label for="remember" class="ml-2 block text-sm text-slate-400 font-medium select-none">
                            Recordarme
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-bold text-red-400 hover:text-red-300 transition-colors">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 rounded-xl border border-transparent shadow-lg text-sm font-bold text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-150 transform active:scale-98 cursor-pointer">
                        Ingresar a la Quiniela
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-slate-800 pt-6 text-center">
                <span class="text-sm text-slate-400">¿Eres colaborador y no tienes cuenta?</span>
                <a href="{{ route('register') }}" class="block mt-2 text-sm font-bold text-red-400 hover:text-red-300 transition-colors">
                    Regístrate aquí &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>
