<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomationRuleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PlatformConnectionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SetupWizardController;
use App\Http\Controllers\TeamController;
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

    // Leads & Sandbox Simulation
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::patch('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('/leads/simulate', [LeadController::class, 'simulateTestLead'])->name('leads.simulate');

    // Setup Wizard (Interactive 5-Step Guided Setup)
    Route::get('/setup', [SetupWizardController::class, 'show'])->name('setup.wizard');
    Route::post('/setup/step1', [SetupWizardController::class, 'processStep1'])->name('setup.wizard.step1');
    Route::post('/setup/step2', [SetupWizardController::class, 'processStep2'])->name('setup.wizard.step2');
    Route::post('/setup/step3', [SetupWizardController::class, 'processStep3'])->name('setup.wizard.step3');
    Route::post('/setup/step4', [SetupWizardController::class, 'processStep4'])->name('setup.wizard.step4');
    Route::post('/setup/reset', [SetupWizardController::class, 'resetDemoData'])->name('setup.reset');

    // Business / Client Management
    Route::resource('clients', ClientController::class);

    // Team & RBAC Access Management
    Route::resource('team', TeamController::class)->only(['index', 'store', 'destroy']);

    // Automation Rules
    Route::get('/automation', [AutomationRuleController::class, 'index'])->name('automation.index');
    Route::post('/automation/rules', [AutomationRuleController::class, 'store'])->name('automation.store');

    // Platform Connections & Webhooks
    Route::get('/connections', [PlatformConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections', [PlatformConnectionController::class, 'store'])->name('connections.store');

    // Visual Guides & Help Center
    Route::get('/guide', [GuideController::class, 'index'])->name('guide.index');

    // Reports
    Route::get('/reports/lead-quality', [ReportController::class, 'leadQuality'])->name('reports.lead_quality');
});
