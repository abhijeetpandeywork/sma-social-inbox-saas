<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\PlatformConnection;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlatformConnectionController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->get('client_id');
        $clients = Client::all();
        $selectedClient = $clientId ? Client::find($clientId) : $clients->first();

        $connections = [];
        if ($selectedClient) {
            $connections = PlatformConnection::withoutGlobalScopes()
                ->where('client_id', $selectedClient->id)
                ->get();
        }

        $webhookUrl = url('/api/webhooks/meta');
        $verifyToken = env('META_VERIFY_TOKEN', 'social_inbox_secret_token');

        return view('connections.index', [
            'clients' => $clients,
            'selectedClientId' => $selectedClient ? $selectedClient->id : null,
            'selectedClient' => $selectedClient,
            'connections' => $connections,
            'webhookUrl' => $webhookUrl,
            'verifyToken' => $verifyToken,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|string|in:instagram,facebook,twitter,youtube,gmb,linkedin',
            'platform_account_id' => 'required|string',
            'access_token' => 'required|string',
            'token_expires_in_days' => 'nullable|integer',
        ]);

        $expiresAt = $request->filled('token_expires_in_days')
            ? Carbon::now()->addDays((int) $request->input('token_expires_in_days'))
            : Carbon::now()->addDays(60);

        PlatformConnection::withoutGlobalScopes()->updateOrCreate(
            [
                'client_id' => $validated['client_id'],
                'platform' => $validated['platform'],
                'platform_account_id' => $validated['platform_account_id'],
            ],
            [
                'access_token' => $validated['access_token'],
                'health_status' => 'healthy',
                'token_expires_at' => $expiresAt,
                'last_successful_call_at' => Carbon::now(),
            ]
        );

        return redirect()->back()->with('success', 'Platform connection configured successfully!');
    }
}
