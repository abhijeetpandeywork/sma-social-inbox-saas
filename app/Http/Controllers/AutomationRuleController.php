<?php

namespace App\Http\Controllers;

use App\Models\AutomationRule;
use App\Models\Client;
use Illuminate\Http\Request;

class AutomationRuleController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->get('client_id');
        $clients = Client::all();

        $rulesQuery = AutomationRule::query();
        if ($clientId) {
            $rulesQuery->where('client_id', $clientId);
        }
        $rules = $rulesQuery->latest()->get();

        return view('automation.index', [
            'clients' => $clients,
            'selectedClientId' => $clientId,
            'rules' => $rules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|string|in:instagram,facebook,twitter,youtube',
            'trigger_type' => 'required|string',
            'action_type' => 'required|string|in:reply_and_hide,reply_only,hide_only',
            'reply_variants' => 'required|array',
            'business_hours_reply' => 'nullable|string',
        ]);

        AutomationRule::create([
            'client_id' => $validated['client_id'],
            'platform' => $validated['platform'],
            'trigger_type' => $validated['trigger_type'],
            'action_type' => $validated['action_type'],
            'reply_template_variants' => $validated['reply_variants'],
            'business_hours_variant' => ['reply_text' => $validated['business_hours_reply']],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Automation rule created successfully.');
    }
}
