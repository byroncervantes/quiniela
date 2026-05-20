<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - QuinMariscal</title>
    
    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind v4 Compiler Hook -->
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at top right, #1e3a8a 0%, #0f172a 70%);
            -webkit-tap-highlight-color: transparent;
        }

        .glass-login {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .glow-input:focus {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
            border-color: #3b82f6;
        }
    </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo -->
        <div class="inline-flex w-16 h-16 rounded-2xl bg-red-600 items-center justify-center text-white font-black text-3xl shadow-xl shadow-red-900/30 mb-4 transform hover:rotate-12 transition-transform duration-300">
            QM
        </div>
        <h2 class="text-3xl font-extrabold tracking-tight text-white">
            Quin<span class="text-red-500">Mariscal</span>
        </h2>
        <p class="mt-2 text-sm text-slate-400 font-medium uppercase tracking-widest">
            Distribuidora Mariscal
        </p>
        <p class="mt-1 text-xs text-slate-500">
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
                        Correo Electrónico Corporativo
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
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
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4.5 w-4.5 rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900">
                        <label for="remember" class="ml-2 block text-sm text-slate-400 font-medium select-none">
                            Recordarme
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 rounded-xl border border-transparent shadow-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 transform active:scale-98 cursor-pointer">
                        Ingresar a la Quiniela
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-slate-800 pt-6 text-center">
                <span class="text-sm text-slate-400">¿Eres colaborador y no tienes cuenta?</span>
                <a href="{{ route('register') }}" class="block mt-2 text-sm font-bold text-blue-400 hover:text-blue-300 transition-colors">
                    Regístrate aquí &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>
