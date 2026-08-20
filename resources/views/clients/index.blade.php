<x-app-layout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manage Businesses & Clients</h1>
            <p class="text-xs text-slate-500">Configure separate client brands, data retention policies, and social connections</p>
        </div>
        <button @click="$dispatch('open-add-client-modal')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-4 py-2 rounded-lg shadow-sm flex items-center gap-1.5 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Add New Business
        </button>
    </div>

    <!-- Client Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        @forelse($clients as $client)
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 hover:shadow-md transition-shadow relative">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $client->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $client->status }}
                        </span>
                        <h3 class="text-base font-bold text-slate-900 mt-1">{{ $client->name }}</h3>
                        <p class="text-[11px] text-slate-400">Created: {{ $client->created_at->format('M d, Y') }}</p>
                    </div>

                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this business? All associated connections and leads will be permanently deleted.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-300 hover:text-red-500 p-1 transition-colors" title="Delete Business">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>

                <div class="space-y-2 py-3 border-y border-slate-100 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Data Retention:</span>
                        <span class="font-medium text-slate-800">{{ $client->data_retention_months }} Months</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Connected Accounts:</span>
                        <span class="font-semibold text-slate-800">{{ $client->platformConnections->count() }} Platforms</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Active Rules:</span>
                        <span class="font-semibold text-slate-800">{{ $client->automationRules->where('is_active', true)->count() }} Rules</span>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <a href="{{ route('connections.index', ['client_id' => $client->id]) }}" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold py-1.5 px-3 rounded-lg text-xs transition-colors">
                        Connect Accounts
                    </a>
                    <a href="{{ route('dashboard', ['client_id' => $client->id]) }}" class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-1.5 px-3 rounded-lg text-xs transition-colors">
                        View Inbox
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No Businesses Configured Yet</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-5">Add your first business or client brand to start connecting Instagram, Facebook, and automating lead capture.</p>
                <button @click="$dispatch('open-add-client-modal')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-5 py-2.5 rounded-lg shadow-sm">
                    + Add Your First Business
                </button>
            </div>
        @endforelse
    </div>

    <!-- Add Client Modal -->
    <div x-data="{ open: false }" @open-add-client-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="open = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Add New Business / Brand</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('clients.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Business Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Acme Fitness, Sai Business" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800 focus:ring-2 focus:ring-amber-500">
                    <p class="text-[11px] text-slate-400 mt-1">The name of your brand or client company.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        <option value="active">Active (Automation Enabled)</option>
                        <option value="paused">Paused</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Data Retention Period (Months)</label>
                    <input type="number" name="data_retention_months" value="12" min="1" max="60" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                    <p class="text-[11px] text-slate-400 mt-1">Number of months to keep customer interaction logs (DPDP compliance).</p>
                </div>

                <div class="pt-3 flex gap-3">
                    <button type="button" @click="open = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 rounded-lg text-xs transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-xs shadow-sm transition-all">
                        Save Business
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
