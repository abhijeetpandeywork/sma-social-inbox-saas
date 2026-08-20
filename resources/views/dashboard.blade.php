<x-app-layout>
    <div x-data="{ testModalOpen: false }">
        <!-- Top Status & Actions Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Unified Social Inbox & Lead Pipeline</h1>
                <p class="text-xs text-slate-500">Real-time captured leads from Instagram comments, DMs, Facebook, Twitter, and YouTube</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('setup.wizard') }}" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-900 border border-amber-500/30 text-xs font-bold px-3 py-2 rounded-lg flex items-center gap-1.5 transition-all">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Setup Wizard
                </a>

                <button @click="testModalOpen = true" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-3.5 py-2 rounded-lg shadow-xs flex items-center gap-1.5 transition-all">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    + Simulate Test Lead
                </button>
            </div>
        </div>

        <!-- System Health Panel -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Selected Client</span>
                    <p class="font-bold text-slate-800 text-sm truncate">{{ $clients->firstWhere('id', $selectedClientId)->name ?? 'All Clients' }}</p>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Cron Queue Status</span>
                    <p class="font-semibold text-emerald-700 flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active (1-Min DB Cron)
                    </p>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Platform Circuit</span>
                    <p class="font-semibold text-emerald-700 flex items-center gap-1.5 mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ $platformHealth->first()->status ?? 'Healthy' }}
                    </p>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Failed / Dead-Letter</span>
                    <p class="font-semibold {{ $failedCount > 0 ? 'text-red-600' : 'text-slate-700' }} mt-0.5">
                        {{ $failedCount }} Issues
                    </p>
                </div>
            </div>
        </div>

        <!-- Kanban Columns -->
        @php
            $columns = [
                'new' => ['name' => 'New Inbound Leads', 'color' => 'border-amber-500', 'bg' => 'bg-amber-50/50'],
                'contacted' => ['name' => 'Contacted / WhatsApp Sent', 'color' => 'border-blue-500', 'bg' => 'bg-blue-50/50'],
                'qualified' => ['name' => 'Qualified Prospects', 'color' => 'border-purple-500', 'bg' => 'bg-purple-50/50'],
                'converted' => ['name' => 'Converted / Won', 'color' => 'border-emerald-500', 'bg' => 'bg-emerald-50/50'],
                'lost' => ['name' => 'Lost / Disqualified', 'color' => 'border-slate-400', 'bg' => 'bg-slate-50/50'],
            ];
            $hasAnyLeads = $leads->count() > 0;
        @endphp

        @if(!$hasAnyLeads)
            <!-- Zero State Banner for Laymen -->
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center mb-8 shadow-xs">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h2 class="text-base font-bold text-slate-800">Your Lead Pipeline is Ready & Clean</h2>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-6">
                    No leads captured yet. As customers comment or send messages on your social pages, leads will appear here in real time.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('setup.wizard') }}" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-5 py-2.5 rounded-lg shadow-xs flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Launch Setup Wizard
                    </a>
                    <button @click="testModalOpen = true" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-5 py-2.5 rounded-lg shadow-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Simulate Test Lead (1-Click)
                    </button>
                    <a href="{{ route('guide.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-lg">
                        View Setup Guides &rarr;
                    </a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($columns as $statusKey => $colMeta)
                <div class="bg-white rounded-xl shadow-xs border border-slate-200 flex flex-col min-h-[480px]">
                    <!-- Column Header -->
                    <div class="p-3 border-b-2 {{ $colMeta['color'] }} flex items-center justify-between {{ $colMeta['bg'] }} rounded-t-xl">
                        <span class="font-bold text-xs text-slate-800">{{ $colMeta['name'] }}</span>
                        <span class="bg-white text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 shadow-2xs">
                            {{ $leads->where('status', $statusKey)->count() }}
                        </span>
                    </div>

                    <!-- Column Cards -->
                    <div class="p-2.5 flex-1 space-y-2.5 overflow-y-auto">
                        @forelse($leads->where('status', $statusKey) as $lead)
                            <div class="bg-white rounded-lg border border-slate-200 p-3 shadow-2xs hover:shadow-sm transition-all relative">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                        {{ $lead->platform === 'instagram' ? 'bg-purple-100 text-purple-700' : ($lead->platform === 'facebook' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $lead->platform }}
                                    </span>

                                    <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                        {{ $lead->score === 'hot' ? 'bg-red-100 text-red-700' : ($lead->score === 'warm' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $lead->score }}
                                    </span>
                                </div>

                                <h4 class="font-bold text-xs text-slate-900 truncate">{{ $lead->contact_name ?: ($lead->contact_handle ?: 'Anonymous Customer') }}</h4>
                                
                                @if($lead->contact_phone)
                                    <p class="text-[11px] font-mono text-emerald-700 font-semibold mt-0.5">
                                        {{ $lead->contact_phone }}
                                    </p>
                                @endif

                                @if($lead->notes)
                                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 italic">
                                        "{{ $lead->notes }}"
                                    </p>
                                @endif

                                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between gap-1">
                                    @if($lead->contact_phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $lead->contact_phone);
                                            if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                                            $waUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode("Hi {$lead->contact_name}, thank you for your enquiry!");
                                        @endphp
                                        <a href="{{ $waUrl }}" target="_blank" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[10px] px-2 py-1 rounded flex items-center gap-1 shadow-2xs">
                                            <span>WhatsApp</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    @endif

                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('leads.show', $lead->id) }}" class="text-[10px] text-slate-500 hover:text-slate-900 px-1.5 py-1 rounded bg-slate-50 hover:bg-slate-100 font-semibold">
                                            Details
                                        </a>

                                        <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Delete this lead?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-red-500 p-1" title="Delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-300 text-xs italic">
                                No leads in this stage
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Simulate Test Lead Modal -->
        <div x-show="testModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="testModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </span>
                        <h3 class="text-base font-bold text-slate-900">Simulate Incoming Test Lead</h3>
                    </div>
                    <button @click="testModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
                </div>

                <form action="{{ route('leads.simulate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Business *</label>
                        <select name="client_id" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ $selectedClientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Platform</label>
                        <select name="platform" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                            <option value="instagram">Instagram Comment / DM</option>
                            <option value="facebook">Facebook Comment</option>
                            <option value="twitter">Twitter / X</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Customer Name</label>
                        <input type="text" name="contact_name" value="Amit Patel" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Customer Phone Number</label>
                        <input type="text" name="contact_phone" value="+91 98765 43210" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Customer Comment / Message</label>
                        <textarea name="comment_text" rows="2" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">Bhai please send price catalog urgent, call or WhatsApp me on +91 98765 43210</textarea>
                    </div>

                    <div class="pt-3 flex gap-3">
                        <button type="button" @click="testModalOpen = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-lg text-xs">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 rounded-lg text-xs shadow-xs">
                            Create Test Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
