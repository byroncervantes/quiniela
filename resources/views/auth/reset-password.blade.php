<!DOCTYPE html>
<html lang="es" class="h-full bg-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - La Quiniela de Todos</title>
    
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
            style="max-width: 130px !important; width: 100% !important; height: auto !important; aspect-ratio: 1/1 !important; object-fit: contain !important;"
            class="mx-auto rounded-3xl shadow-2xl border border-red-500/20 mb-4 transform hover:scale-105 transition-transform duration-300">
        
        <h2 class="text-3xl font-black tracking-tight text-white uppercase">
            La Quiniela <span class="text-red-500">de Todos</span>
        </h2>
        <p class="mt-2 text-xs text-slate-400 font-bold uppercase tracking-widest leading-relaxed">
            Restablecer Contraseña
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="glass-login py-8 px-6 rounded-3xl sm:px-10">
            
            <form class="space-y-6" action="{{ route('password.update') }}" method="POST">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-300">
                        Correo Electrónico
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', $email) }}"
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="usuario@distmariscal.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-400 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-300">
                        Nueva Contraseña
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Mínimo 6 caracteres">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-300">
                        Confirmar Nueva Contraseña
                    </label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="glow-input block w-full px-4 py-3 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Repita la nueva contraseña">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 rounded-xl border border-transparent shadow-lg text-sm font-bold text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-150 transform active:scale-98 cursor-pointer">
                        Restablecer Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
