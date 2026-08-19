<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Lead;
use Carbon\Carbon;

class LeadReportService
{
    /**
     * Generate weekly AI-assisted lead quality report data for a client.
     */
    public static function generateReportData(Client $client, int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);

        $leads = Lead::withoutGlobalScopes()
            ->where('client_id', $client->id)
            ->where('captured_at', '>=', $startDate)
            ->get();

        $totalLeads = count($leads);
        $hotCount = count($leads->where('score', 'hot'));
        $warmCount = count($leads->where('score', 'warm'));
        $coldCount = count($leads->where('score', 'cold'));

        $convertedCount = count($leads->where('status', 'converted'));
        $conversionRate = $totalLeads > 0 ? round(($convertedCount / $totalLeads) * 100, 1) : 0;

        $summaryText = "During the past {$days} days, {$totalLeads} total leads were captured for {$client->name}. " .
            "{$hotCount} leads showed High Buying Intent (Hot) with phone numbers captured. " .
            "Conversion rate currently stands at {$conversionRate}%.";

        return [
            'client_name' => $client->name,
            'report_date' => Carbon::now()->format('F d, Y'),
            'days_covered' => $days,
            'total_leads' => $totalLeads,
            'hot_count' => $hotCount,
            'warm_count' => $warmCount,
            'cold_count' => $coldCount,
            'converted_count' => $convertedCount,
            'conversion_rate' => $conversionRate,
            'summary' => $summaryText,
            'leads' => $leads,
        ];
    }
}
