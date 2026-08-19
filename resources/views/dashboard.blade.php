<x-app-layout>
    <!-- System Health Panel Banner -->
    <div class="mb-6 bg-slate-900 text-slate-100 rounded-xl p-5 shadow-sm border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-white">System Health & Cron Processing Status</h2>
                    <p class="text-xs text-slate-400">Shared hosting cron processing (1-min schedule) with DB cache locks & rate limiters</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <!-- Last Cron Queue Execution -->
                <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
                    <span class="text-slate-400">Last Cron Run:</span>
                    <span class="font-mono font-semibold ml-1.5 {{ $lastQueueRun ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $lastQueueRun ? $lastQueueRun->created_at->diffForHumans() : 'No runs logged yet' }}
                    </span>
                </div>

                <!-- Platform Circuit Status -->
                <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 flex items-center gap-2">
                    <span class="text-slate-400">Platform Health:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Healthy
                    </span>
                </div>

                <!-- Failed Actions Counter -->
                <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700">
                    <span class="text-slate-400">Failed Actions:</span>
                    <span class="font-mono font-semibold ml-1.5 {{ $failedActionsCount > 0 ? 'text-red-400' : 'text-slate-300' }}">
                        {{ $failedActionsCount }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Lead Pipeline (Kanban Board) -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Unified Lead Pipeline</h1>
            <p class="text-xs text-slate-500">Auto-captured leads from Instagram and Facebook with phone regex & Claude intent scoring</p>
        </div>
        <div class="text-xs font-semibold px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
            Total Leads: {{ count($leads) }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        @php
            $statuses = [
                'new' => ['title' => 'New Leads', 'color' => 'bg-blue-500'],
                'contacted' => ['title' => 'Contacted', 'color' => 'bg-amber-500'],
                'qualified' => ['title' => 'Qualified', 'color' => 'bg-purple-500'],
                'converted' => ['title' => 'Converted', 'color' => 'bg-emerald-500'],
                'lost' => ['title' => 'Lost', 'color' => 'bg-slate-400'],
            ];
        @endphp

        @foreach($statuses as $key => $col)
        <div class="bg-slate-200/60 rounded-xl p-3 flex flex-col min-h-[500px] border border-slate-300/60">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-300">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $col['color'] }}"></span>
                    <h3 class="font-semibold text-xs text-slate-700 uppercase tracking-wider">{{ $col['title'] }}</h3>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-white px-2 py-0.5 rounded-full shadow-xs">
                    {{ count($kanban[$key] ?? []) }}
                </span>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto">
                @forelse($kanban[$key] ?? [] as $lead)
                <div class="bg-white rounded-lg p-3 shadow-sm border border-slate-200 hover:border-slate-300 transition-all">
                    <div class="flex items-start justify-between mb-1.5">
                        <span class="text-xs font-semibold text-slate-800">{{ $lead->contact_name ?? 'Anonymous User' }}</span>
                        <!-- Score Badge -->
                        @if($lead->score === 'hot')
                            <span class="text-[10px] uppercase font-bold bg-red-100 text-red-700 px-1.5 py-0.5 rounded">Hot</span>
                        @elseif($lead->score === 'warm')
                            <span class="text-[10px] uppercase font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">Warm</span>
                        @else
                            <span class="text-[10px] uppercase font-bold bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">Cold</span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-500 mb-2 flex items-center gap-1">
                        <span class="capitalize font-medium text-slate-700">{{ $lead->platform }}</span>
                        <span>•</span>
                        <span>{{ $lead->captured_at ? $lead->captured_at->diffForHumans() : $lead->created_at->diffForHumans() }}</span>
                    </div>

                    @if($lead->contact_phone)
                    <div class="mb-2">
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->contact_phone) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-200 hover:bg-emerald-100">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654z"/></svg>
                            <span>WhatsApp Handoff</span>
                        </a>
                    </div>
                    @endif

                    @if($lead->notes)
                    <p class="text-[11px] text-slate-600 bg-slate-50 p-1.5 rounded mb-2.5 line-clamp-2 border border-slate-100">
                        {{ $lead->notes }}
                    </p>
                    @endif

                    <!-- Quick Status Selector -->
                    <form action="/leads/{{ $lead->id }}" method="POST" class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="text-[11px] bg-slate-50 border border-slate-200 rounded px-1.5 py-1 text-slate-700 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                            <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                        <a href="/leads/{{ $lead->id }}" class="text-[11px] font-medium text-amber-600 hover:text-amber-700">View Details &rarr;</a>
                    </form>
                </div>
                @empty
                <div class="text-center py-8 text-xs text-slate-400 italic">No leads in this column</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</x-app-layout>
