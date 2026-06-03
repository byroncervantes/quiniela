<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        if ($user->status === 'pending') {
            return back()->withErrors([
                'email' => 'Tu cuenta está pendiente de aprobación por el administrador.',
            ])->onlyInput('email');
        }

        if ($user->status === 'blocked') {
            return back()->withErrors([
                'email' => 'Tu cuenta ha sido bloqueada. Por favor, contacta a soporte.',
            ])->onlyInput('email');
        }

        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        // Redirect based on role or to dashboard
        if (in_array($user->role, ['super_admin', 'admin_quiniela', 'moderador'])) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        if (!AppSetting::getValue('registration_enabled', true)) {
            return redirect()->route('login')->with('error', 'El registro de usuarios está deshabilitado temporalmente.');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $branches = \App\Models\Branch::where('is_active', true)->orderBy('name', 'asc')->get();

        $departments = \App\Models\Department::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('auth.register', compact('branches', 'departments'));
    }

    public function register(Request $request)
    {
        if (!AppSetting::getValue('registration_enabled', true)) {
            return redirect()->route('login')->with('error', 'El registro de usuarios está deshabilitado temporalmente.');
        }

        $allowedDomains = AppSetting::getValue('allowed_email_domains', '');
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                function ($attribute, $value, $fail) use ($allowedDomains) {
                    if (!empty($allowedDomains)) {
                        $domains = array_map('trim', explode(',', strtolower($allowedDomains)));
                        $emailDomain = strtolower(substr(strrchr($value, "@"), 1));
                        if (!in_array($emailDomain, $domains)) {
                            $fail('El correo electrónico debe pertenecer a uno de los dominios autorizados de Distribuidora Mariscal (' . implode(', ', $domains) . ').');
                        }
                    }
                }
            ],
            'phone' => 'nullable|string|max:30',
            'employee_code' => 'required|string|min:13|max:50|unique:users,employee_code|regex:/^[0-9]+$/',
            'department_id' => 'required|exists:departments,id',
            'branch_id' => 'required|exists:branches,id',
            'password' => 'required|string|min:6|confirmed',
            'accepted_terms' => 'accepted',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $requiresApproval = AppSetting::getValue('registration_requires_approval', false);
        $status = $requiresApproval ? 'pending' : 'active';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'participante',
            'status' => $status,
            'phone' => $request->phone,
            'employee_code' => $request->employee_code,
            'department_id' => $request->department_id,
            'branch_id' => $request->branch_id,
            'company' => 'Distribuidora Mariscal',
            'accepted_terms' => true,
        ]);

        if ($requiresApproval) {
            return redirect()->route('login')->with('status', 'Registro exitoso. Tu cuenta está pendiente de aprobación por parte del administrador de La Quiniela de Todos.');
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Bienvenido a La Quiniela de Todos! Te has registrado con éxito.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'No encontramos ningún usuario registrado con este correo electrónico.'
        ]);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with('status', '¡Te hemos enviado por correo el enlace para restablecer tu contraseña!')
                    : back()->withErrors(['email' => 'No pudimos enviar el correo de recuperación.']);
    }

    public function showResetPassword(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.exists' => 'El correo electrónico no coincide con nuestros registros.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.'
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', 'Tu contraseña ha sido restablecida. Ya puedes iniciar sesión.')
                    : back()->withErrors(['email' => 'El token de restablecimiento de contraseña es inválido o ha expirado.']);
    }
}
