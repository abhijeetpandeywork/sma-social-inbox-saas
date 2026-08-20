<?php

namespace App\Http\Controllers;

use App\Models\AutomationRule;
use App\Models\Client;
use App\Models\PlatformConnection;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class SetupWizardController extends Controller
{
    public function show(Request $request)
    {
        $step = (int) $request->get('step', 1);
        $clients = Client::orderBy('name')->get();
        $firstClient = $clients->first();
        $admin = TeamMember::where('role', 'Agency Admin')->first() ?? auth()->user();

        $webhookUrl = url('/api/webhooks/meta');
        $verifyToken = config('services.meta.verify_token', 'social_inbox_secret_token');

        $activeConnections = $firstClient ? PlatformConnection::where('client_id', $firstClient->id)->get() : collect();
        $rules = $firstClient ? AutomationRule::where('client_id', $firstClient->id)->get() : collect();

        return view('setup.wizard', compact(
            'step',
            'clients',
            'firstClient',
            'admin',
            'webhookUrl',
            'verifyToken',
            'activeConnections',
            'rules'
        ));
    }

    public function processStep1(Request $request)
    {
        $validated = $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'nullable|string|min:6',
        ]);

        $admin = TeamMember::where('role', 'Agency Admin')->first();
        if ($admin) {
            $admin->name = $validated['admin_name'];
            $admin->email = $validated['admin_email'];
            if (!empty($validated['admin_password'])) {
                $admin->password = Hash::make($validated['admin_password']);
            }
            $admin->save();
        }

        return redirect()->route('setup.wizard', ['step' => 2])->with('success', 'Agency profile updated! Now let\'s add your business.');
    }

    public function processStep2(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'data_retention_months' => 'required|integer|min:1|max:60',
        ]);

        $client = Client::create([
            'agency_id' => auth()->user()->agency_id ?? 1,
            'name' => $validated['business_name'],
            'status' => 'active',
            'data_retention_months' => $validated['data_retention_months'],
        ]);

        return redirect()->route('setup.wizard', ['step' => 3, 'client_id' => $client->id])
            ->with('success', "Business '{$client->name}' created! Next, connect your social platform.");
    }

    public function processStep3(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|string',
            'platform_account_id' => 'required|string|max:255',
            'access_token' => 'required|string',
            'token_expires_in_days' => 'nullable|integer',
        ]);

        PlatformConnection::updateOrCreate(
            ['client_id' => $validated['client_id'], 'platform' => $validated['platform']],
            [
                'platform_account_id' => $validated['platform_account_id'],
                'access_token' => $validated['access_token'],
                'health_status' => 'healthy',
                'token_expires_at' => Carbon::now()->addDays($validated['token_expires_in_days'] ?? 60),
                'last_successful_call_at' => now(),
            ]
        );

        return redirect()->route('setup.wizard', ['step' => 4, 'client_id' => $validated['client_id']])
            ->with('success', "Social account connected! Now configure your auto-replies and WhatsApp alerts.");
    }

    public function processStep4(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|string',
            'reply_variant_1' => 'required|string',
            'reply_variant_2' => 'nullable|string',
            'business_hours_reply' => 'nullable|string',
            'action_type' => 'required|in:reply_only,reply_and_hide',
        ]);

        $variants = array_filter([$validated['reply_variant_1'], $validated['reply_variant_2'] ?? null]);

        AutomationRule::updateOrCreate(
            ['client_id' => $validated['client_id'], 'platform' => $validated['platform']],
            [
                'trigger_type' => 'phone_or_buying_intent',
                'action_type' => $validated['action_type'],
                'reply_template_variants' => array_values($variants),
                'business_hours_variant' => $validated['business_hours_reply'] ? ['reply_text' => $validated['business_hours_reply']] : null,
                'is_active' => true,
            ]
        );

        return redirect()->route('setup.wizard', ['step' => 5, 'client_id' => $validated['client_id']])
            ->with('success', "Automation rules configured! Let's test and complete setup.");
    }

    public function resetDemoData(Request $request)
    {
        Artisan::call('app:reset-demo-data');

        return redirect()->route('dashboard')->with('success', 'Database cleared to a fresh clean slate! All mock data has been removed.');
    }
}
