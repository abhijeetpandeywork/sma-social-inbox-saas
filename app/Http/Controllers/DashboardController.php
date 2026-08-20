<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\FailedAction;
use App\Models\Lead;
use App\Models\PlatformConnection;
use App\Models\PlatformHealth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedClientId = $request->get('client_id');

        $clients = Client::all();
        if (!$selectedClientId && $clients->isNotEmpty()) {
            $selectedClientId = $clients->first()->id;
        }

        // Leads scoped by active client
        $leadsQuery = Lead::query();
        if ($selectedClientId) {
            $leadsQuery->where('client_id', $selectedClientId);
        }
        $leads = $leadsQuery->latest()->get();

        // Group leads by status for Kanban Board
        $kanban = [
            'new' => $leads->where('status', 'new'),
            'contacted' => $leads->where('status', 'contacted'),
            'qualified' => $leads->where('status', 'qualified'),
            'converted' => $leads->where('status', 'converted'),
            'lost' => $leads->where('status', 'lost'),
        ];

        // System Health Panel Metrics
        $lastQueueRun = ActionLog::where('action_type', 'process_webhook')->latest('created_at')->first();
        $platformHealth = PlatformHealth::all();
        $failedActionsCount = FailedAction::count();
        $connections = PlatformConnection::all();

        return view('dashboard', [
            'user' => $user,
            'clients' => $clients,
            'selectedClientId' => $selectedClientId,
            'leads' => $leads,
            'kanban' => $kanban,
            'lastQueueRun' => $lastQueueRun,
            'platformHealth' => $platformHealth,
            'failedActionsCount' => $failedActionsCount,
            'failedCount' => $failedActionsCount,
            'connections' => $connections,
        ]);
    }
}
