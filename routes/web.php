<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard/login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Recovery routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Collaborator dashboard area (secured under auth middleware inside DashboardController)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::post('/pools/{id}/join', [DashboardController::class, 'joinPool'])->name('pools.join');
    Route::get('/pools/{slug}/predictions', [DashboardController::class, 'predictions'])->name('pools.predictions');
    Route::post('/pools/{slug}/predictions', [DashboardController::class, 'savePredictionsBulk'])->name('pools.predictions.bulk-save');
    Route::post('/pools/{slug}/predictions/save', [DashboardController::class, 'savePredictionAjax'])->name('pools.predictions.save-ajax');
    Route::get('/pools/{slug}/ranking', [DashboardController::class, 'ranking'])->name('pools.ranking');
    Route::get('/matches', [DashboardController::class, 'matches'])->name('matches');
    Route::get('/rules', [DashboardController::class, 'rules'])->name('rules');
    Route::get('/profile', [DashboardController::class, 'showProfile'])->name('profile');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
});

// System cache clearer
Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return response()->json(['status' => 'success', 'message' => 'Todas las cachés de Laravel han sido limpiadas.']);
});

// Temporary test route to debug email errors in production
Route::get('/test-mail', function (\Illuminate\Http\Request $request) {
    $to = $request->query('to', 'quiniela@dm.com.gt');
    try {
        Mail::raw('Este es un correo de prueba de La Quiniela de Todos.', function ($message) use ($to) {
            $message->to($to)
                    ->subject('Prueba de Correo Dinámica - La Quiniela de Todos');
        });
        return response()->json([
            'status' => 'success', 
            'message' => "El correo de prueba fue enviado con éxito a {$to}. Por favor revisa la bandeja de entrada y la carpeta de spam."
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error', 
            'message' => $e->getMessage(),
            'class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
});


