<x-app-layout>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Weekly Lead Quality Report</h1>
            <p class="text-xs text-slate-500">Automated performance and lead capture metrics for {{ $report['client_name'] }}</p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-sm">
            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print / Export PDF
        </button>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Total Leads</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $report['total_leads'] }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
            <span class="text-xs text-red-500 font-semibold uppercase tracking-wider">Hot Leads (Phone Captured)</span>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ $report['hot_count'] }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
            <span class="text-xs text-amber-500 font-semibold uppercase tracking-wider">Warm Leads</span>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $report['warm_count'] }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
            <span class="text-xs text-emerald-500 font-semibold uppercase tracking-wider">Conversion Rate</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $report['conversion_rate'] }}%</div>
        </div>
    </div>

    <!-- AI Summary & Table -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-2">AI Performance Summary</h2>
        <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100 mb-6">
            {{ $report['summary'] }}
        </p>

        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Captured Lead Log</h2>
        <table class="w-full text-xs text-left text-slate-700">
            <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold tracking-wider">
                <tr>
                    <th class="p-2.5">Name</th>
                    <th class="p-2.5">Platform</th>
                    <th class="p-2.5">Score</th>
                    <th class="p-2.5">Status</th>
                    <th class="p-2.5">Captured At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($report['leads'] as $l)
                <tr>
                    <td class="p-2.5 font-medium">{{ $l->contact_name ?? 'Anonymous' }}</td>
                    <td class="p-2.5 capitalize">{{ $l->platform }}</td>
                    <td class="p-2.5 uppercase font-bold text-amber-600">{{ $l->score }}</td>
                    <td class="p-2.5 capitalize">{{ $l->status }}</td>
                    <td class="p-2.5 text-slate-400">{{ $l->captured_at ? $l->captured_at->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-slate-400">No leads captured in the specified period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
