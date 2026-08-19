<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\PiiAccessLog;
use App\Services\LeadDetectionService;
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
}
