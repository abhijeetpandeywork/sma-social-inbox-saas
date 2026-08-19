<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\FailedAction;
use App\Models\PlatformHealth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * System Health Check Endpoint (/health)
     */
    public function check(): JsonResponse
    {
        $dbStatus = 'healthy';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'unhealthy: ' . $e->getMessage();
        }

        // Timestamp of last successful queue job execution
        $lastQueueRun = ActionLog::where('action_type', 'process_webhook')
            ->where('status', 'success')
            ->latest('created_at')
            ->first();

        $lastQueueRunAt = $lastQueueRun ? $lastQueueRun->created_at->toIso8601String() : null;
        $cronHealth = 'healthy';

        if ($lastQueueRun && $lastQueueRun->created_at->diffInMinutes(Carbon::now()) > 5) {
            $cronHealth = 'stale_cron_alert'; // Cron may have stopped running
        }

        $platformHealthRecords = PlatformHealth::all()->pluck('status', 'platform');
        $failedActionsCount = FailedAction::count();

        $overallStatus = ($dbStatus === 'healthy' && $cronHealth === 'healthy') ? 'healthy' : 'degraded';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => Carbon::now()->toIso8601String(),
            'database' => $dbStatus,
            'cron_queue' => [
                'status' => $cronHealth,
                'last_successful_run_at' => $lastQueueRunAt,
            ],
            'platforms' => $platformHealthRecords,
            'failed_actions_count' => $failedActionsCount,
        ], $overallStatus === 'healthy' ? 200 : 503);
    }
}
