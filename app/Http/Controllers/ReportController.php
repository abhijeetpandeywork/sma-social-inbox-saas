<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\LeadReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function leadQuality(Request $request)
    {
        $clientId = $request->get('client_id');
        $client = $clientId ? Client::find($clientId) : Client::first();

        if (!$client) {
            return redirect('/dashboard')->with('error', 'No active clients found.');
        }

        $report = LeadReportService::generateReportData($client, 7);

        return view('reports.lead_quality', [
            'report' => $report,
            'clients' => Client::all(),
            'selectedClientId' => $client->id,
        ]);
    }
}
