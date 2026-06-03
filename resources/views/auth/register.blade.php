<!DOCTYPE html>
<html lang="es" class="h-full bg-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Colaborador - La Quiniela de Todos</title>
    
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

        .glass-register {
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

        .select-dark, .select-dark option {
            background-color: #0f172a !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl text-center">
        <img src="{{ asset('img/la_quiniela_de_todos.jpg') }}" alt="Logo La Quiniela de Todos" 
            style="max-width: 120px !important; width: 100% !important; height: auto !important; aspect-ratio: 1/1 !important; object-fit: contain !important;"
            class="mx-auto rounded-3xl shadow-2xl border border-red-500/20 mb-4 transform hover:scale-105 transition-transform duration-300">
        
        <h2 class="text-3xl font-black tracking-tight text-white uppercase">
            Registro de Colaborador
        </h2>
        <p class="mt-1.5 text-xs text-slate-400 font-bold uppercase tracking-widest leading-relaxed">
            Únete a La Quiniela de Todos 2026
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-2xl">
        <div class="glass-register py-8 px-6 rounded-3xl sm:px-10">
            
            <form class="space-y-6" action="{{ route('register') }}" method="POST">
                @csrf

                <!-- Registration Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-300">
                            Nombre Completo
                        </label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Ej. Juan Pérez">
                        @error('name')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-300">
                            Correo Corporativo / Personal
                        </label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="juan.perez@distmariscal.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-300">
                            Teléfono / WhatsApp
                        </label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Ej. 55554444">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Employee Code -->
                    <div>
                        <label for="employee_code" class="block text-sm font-semibold text-slate-300">
                            Documento Identificación (DPI)
                        </label>
                        <input id="employee_code" name="employee_code" type="text" required value="{{ old('employee_code') }}"
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Ej. 1234567890123 (13 dígitos)">
                        @error('employee_code')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Branch -->
                    <div>
                        <label for="branch_id" class="block text-sm font-semibold text-slate-300">
                            Sucursal
                        </label>
                        <select id="branch_id" name="branch_id" required
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:outline-none transition-all duration-200 text-sm select-dark">
                            <option value="">Selecciona Sucursal...</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department_id" class="block text-sm font-semibold text-slate-300">
                            Departamento
                        </label>
                        <select id="department_id" name="department_id" required
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white focus:outline-none transition-all duration-200 text-sm select-dark">
                            <option value="">Selecciona Departamento...</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-300">
                            Contraseña
                        </label>
                        <input id="password" name="password" type="password" required
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Mínimo 6 caracteres">
                        @error('password')
                            <p class="mt-1 text-xs text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-300">
                            Confirmar Contraseña
                        </label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="glow-input mt-1 block w-full px-4 py-2.5 rounded-xl bg-slate-900/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none transition-all duration-200 text-sm"
                            placeholder="Repite contraseña">
                    </div>

                </div>

                <!-- Terms and Conditions Checkbox -->
                <div class="flex items-start mt-4">
                    <div class="flex items-center h-5">
                        <input id="accepted_terms" name="accepted_terms" type="checkbox" required
                            class="h-4.5 w-4.5 rounded border-slate-700 bg-slate-900 text-red-600 focus:ring-red-500 focus:ring-offset-slate-950">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="accepted_terms" class="font-medium text-slate-300">
                            Acepto los <span class="text-red-400 underline cursor-pointer hover:text-red-300 font-bold" onclick="document.getElementById('terms-modal').style.display='flex'">términos y condiciones</span> de la actividad recreativa interna.
                        </label>
                        @error('accepted_terms')
                            <p class="mt-1 text-xs text-red-400 font-semibold">Debes aceptar los términos para continuar.</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3.5 px-4 rounded-xl border border-transparent shadow-lg text-sm font-bold text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-150 transform active:scale-98 cursor-pointer">
                        Completar Registro
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-slate-800 pt-6 text-center">
                <span class="text-sm text-slate-400">¿Ya tienes cuenta?</span>
                <a href="{{ route('login') }}" class="block mt-2 text-sm font-bold text-red-400 hover:text-red-300 transition-colors">
                    Inicia Sesión aquí &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Dark Terms and Conditi    <div id="terms-modal" class="fixed inset-0 z-50 bg-black/75 flex items-center justify-center p-4" style="display: none;">
        <div class="bg-slate-900 rounded-3xl border border-slate-800 max-w-lg w-full overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/50">
                <h3 class="text-lg font-bold text-white">Términos y Condiciones - La Quiniela de Todos</h3>
                <button type="button" class="text-slate-400 hover:text-white font-extrabold text-xl p-1" onclick="document.getElementById('terms-modal').style.display='none'">&times;</button>
            </div>
            <div class="p-6 text-sm text-slate-300 overflow-y-auto space-y-4 leading-relaxed">
                <p class="font-semibold text-white">1. Propósito de la Quiniela</p>
                <p>La Quiniela de Todos es una plataforma interna organizada con fines exclusivamente recreativos, de integración y esparcimiento para los colaboradores activos de <strong>Distribuidora Mariscal</strong> en el contexto del Mundial de Fútbol FIFA 2026.</p>
                
                <p class="font-semibold text-white">2. Sin Dinero Real / Apuestas Reguladas</p>
                <p>Queda estrictamente prohibida la captación de apuestas con dinero en efectivo o cualquier otra forma de azar comercial a través de esta plataforma. Cualquier incentivo, premio o reconocimiento final es meramente simbólico e institucional, auspiciado de manera autónoma.</p>
                
                <p class="font-semibold text-white">3. Participación Autorizada</p>
                <p>La participación está reservada para colaboradores registrados. Cada colaborador tiene derecho a una sola cuenta. Cuentas múltiples o sospechosas serán bloqueadas de forma irreversible por la administración.</p>
 
                <p class="font-semibold text-white">4. Plazo y Cierre de Pronósticos</p>
                <p>La predicción de cada partido se bloquea estrictamente de manera automática en el momento en que inicia el cotejo deportivo según el horario programado. Ningún administrador puede ingresar marcadores fuera de este límite para garantizar transparencia absoluta.</p>
            </div>
            <div class="p-4 bg-slate-950 border-t border-slate-800 text-right">
                <button type="button" class="px-5 py-2 rounded-xl bg-red-600 text-white font-bold text-sm hover:bg-red-500 transition-colors" onclick="document.getElementById('terms-modal').style.display='none'">Cerrar y Aceptar</button>
            </div>
        </div>
    </div>  </div>
        </div>
    </div>
</body>
</html>
