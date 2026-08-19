<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <a href="/dashboard" class="inline-flex items-center text-xs font-medium text-amber-600 hover:text-amber-700 mb-4">
            &larr; Back to Lead Pipeline
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $lead->contact_name ?? 'Anonymous User' }}</h1>
                    <p class="text-xs text-slate-500">Captured on {{ $lead->platform }} • {{ $lead->captured_at ? $lead->captured_at->format('M d, Y h:i A') : 'N/A' }}</p>
                </div>
                <span class="text-xs uppercase font-bold px-2.5 py-1 rounded {{ $lead->score === 'hot' ? 'bg-red-100 text-red-700' : ($lead->score === 'warm' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                    {{ $lead->score }} Lead
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Contact Phone (AES Encrypted at Rest)</label>
                    <div class="text-sm font-mono font-semibold text-slate-800 bg-slate-50 p-2.5 rounded border border-slate-200">
                        {{ $lead->contact_phone ?? 'No phone number provided' }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Social Handle</label>
                    <div class="text-sm font-semibold text-slate-800 bg-slate-50 p-2.5 rounded border border-slate-200">
                        {{ $lead->contact_handle ? '@' . $lead->contact_handle : 'N/A' }}
                    </div>
                </div>
            </div>

            @if($whatsAppUrl)
            <div class="mb-6">
                <a href="{{ $whatsAppUrl }}" target="_blank" class="inline-flex items-center justify-center gap-2 w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-lg shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654z"/></svg>
                    Open WhatsApp Chat with Lead
                </a>
            </div>
            @endif

            <form action="/leads/{{ $lead->id }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Pipeline Status</label>
                    <select name="status" class="w-full text-sm bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ $lead->status === 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Internal Notes & Comment Details</label>
                    <textarea name="notes" rows="4" class="w-full text-sm bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">{{ $lead->notes }}</textarea>
                </div>

                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-5 py-2.5 rounded-lg text-sm transition-all shadow-sm">
                    Save Lead Details
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
