<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\PiiAccessLog;
use App\Services\LeadDetectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Show Lead Details & Log PII Access
     */
    public function show(Lead $lead)
    {
        $user = Auth::user();

        // Audit Log for PII View (DPDP Act Compliance)
        if ($user) {
            PiiAccessLog::create([
                'user_id' => $user->id,
                'lead_id' => $lead->id,
                'action' => 'view_pii',
                'created_at' => now(),
            ]);
        }

        $whatsAppUrl = LeadDetectionService::getWhatsAppLink($lead->contact_phone);

        return view('leads.show', [
            'lead' => $lead,
            'whatsAppUrl' => $whatsAppUrl,
        ]);
    }

    /**
     * Update Lead Status / Assignment
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,qualified,converted,lost',
            'score' => 'nullable|string|in:hot,warm,cold',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->back()->with('success', 'Lead status updated successfully.');
    }

    /**
     * Simulate a manual / test incoming lead (for user verification)
     */
    public function simulateTestLead(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'platform' => 'required|in:instagram,facebook,twitter,youtube,gmb,linkedin',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:25',
            'comment_text' => 'required|string',
        ]);

        // Evaluate intent & score using LeadDetectionService
        $score = LeadDetectionService::classifyIntent($validated['comment_text']);
        $extractedPhone = LeadDetectionService::extractPhoneNumber($validated['contact_phone']) ?? $validated['contact_phone'];

        $lead = Lead::create([
            'client_id' => $validated['client_id'],
            'platform' => $validated['platform'],
            'contact_name' => $validated['contact_name'],
            'contact_phone' => $extractedPhone,
            'contact_handle' => strtolower(str_replace(' ', '_', $validated['contact_name'])),
            'source_comment_id' => 'test_' . uniqid(),
            'status' => 'new',
            'score' => $score,
            'captured_at' => Carbon::now(),
            'notes' => 'Test Lead generated manually. Comment: "' . $validated['comment_text'] . '"',
        ]);

        return redirect()->route('dashboard', ['client_id' => $lead->client_id])
            ->with('success', "Test Lead for '{$lead->contact_name}' created successfully! Score: " . strtoupper($lead->score));
    }

    /**
     * Delete Lead
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('dashboard')->with('success', 'Lead deleted.');
    }
}
