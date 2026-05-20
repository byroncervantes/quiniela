@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    
    <!-- Header -->
    <div class="glass-card p-6 rounded-3xl border border-slate-100 flex items-center gap-2">
        <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Ajustes Personales</span>
            <h1 class="text-2xl font-extrabold text-[#0f2240]">Mi Perfil de Colaborador</h1>
        </div>
    </div>

    <!-- Form card -->
    <div class="glass-card rounded-3xl border border-slate-100 p-6 sm:p-8 shadow-sm">
        
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                
                <!-- Full Name -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Nombre Completo
                    </label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
                        class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm text-slate-700">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email (Read-only / Unchangeable) -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Correo Corporativo (No modificable)
                    </label>
                    <input type="email" disabled value="{{ $user->email }}"
                        class="block w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-400 cursor-not-allowed">
                </div>

                <!-- Employee code (Read-only / Unchangeable) -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Código de Empleado (No modificable)
                    </label>
                    <input type="text" disabled value="{{ $user->employee_code }}"
                        class="block w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-400 cursor-not-allowed">
                </div>

                <!-- WhatsApp Phone -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Teléfono / WhatsApp
                    </label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                        class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm text-slate-700">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch -->
                <div>
                    <label for="branch" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Sucursal
                    </label>
                    <select id="branch" name="branch" required
                        class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm text-slate-700">
                        @foreach($branches as $b)
                            <option value="{{ $b }}" {{ old('branch', $user->branch) == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                    @error('branch')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Departamento
                    </label>
                    <select id="department" name="department" required
                        class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm text-slate-700">
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ old('department', $user->department) == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('department')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 border-t border-slate-100 my-2 pt-4">
                    <h3 class="text-sm font-bold text-[#0f2240] mb-1">Cambiar Contraseña</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Dejar vacío si no deseas modificar tu contraseña actual.</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Nueva Contraseña
                    </label>
                    <input id="password" name="password" type="password"
                        class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm text-slate-700"
                        placeholder="Mínimo 6 caracteres">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Confirmar Nueva Contraseña
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                        class="block w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all text-sm text-slate-700"
                        placeholder="Repite la nueva contraseña">
                </div>

            </div>

            <!-- Footer Save -->
            <div class="border-t border-slate-100 pt-5 flex justify-end gap-3">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs text-center shadow-sm hover:bg-slate-50 transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs text-center shadow-md transition-all cursor-pointer">
                    Guardar Cambios
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
