<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomationRuleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PlatformConnectionController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

// System Health Endpoint
Route::get('/health', [HealthController::class, 'check']);

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/2fa/prompt', [AuthController::class, 'show2faPrompt'])->name('2fa.prompt');
Route::post('/2fa/verify', [AuthController::class, 'verify2fa']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Application Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');

    Route::get('/automation', [AutomationRuleController::class, 'index'])->name('automation.index');
    Route::post('/automation/rules', [AutomationRuleController::class, 'store'])->name('automation.store');

    Route::get('/connections', [PlatformConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections', [PlatformConnectionController::class, 'store'])->name('connections.store');

    Route::get('/reports/lead-quality', [ReportController::class, 'leadQuality'])->name('reports.lead_quality');
});
