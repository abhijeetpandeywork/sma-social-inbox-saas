<x-app-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Automation Rules Builder</h1>
        <p class="text-xs text-slate-500">Configure auto-reply template variants (A/B testing) and business-hours-aware variants</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Rule Creation Form -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Create Automation Rule</h2>

            <form action="/automation/rules" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Client</label>
                    <select name="client_id" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Platform</label>
                    <select name="platform" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                        <option value="instagram">Instagram Business</option>
                        <option value="facebook">Facebook Page</option>
                        <option value="twitter">Twitter / X</option>
                        <option value="youtube">YouTube</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Trigger Type</label>
                    <input type="text" name="trigger_type" value="phone_or_buying_intent" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Action Type</label>
                    <select name="action_type" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                        <option value="reply_and_hide">Reply & Hide Comment</option>
                        <option value="reply_only">Reply Only</option>
                        <option value="hide_only">Hide Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reply Variant A (Standard)</label>
                    <textarea name="reply_variants[]" rows="2" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800" placeholder="Thanks for reaching out! Check your DMs for details."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reply Variant B (A/B Test)</label>
                    <textarea name="reply_variants[]" rows="2" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800" placeholder="Hi! We have sent you full price details on DM."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">After-Hours Reply Variant</label>
                    <textarea name="business_hours_reply" rows="2" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800" placeholder="Thanks! Our office is closed now, but we will contact you first thing in the morning."></textarea>
                </div>

                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-xs transition-all shadow-sm">
                    Save Automation Rule
                </button>
            </form>
        </div>

        <!-- Rules List -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-2">Active Automation Rules</h2>

            @forelse($rules as $rule)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-sm text-slate-800 capitalize">{{ $rule->platform }}</span>
                        <span class="text-xs text-slate-400">• {{ $rule->trigger_type }}</span>
                    </div>
                    <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                        {{ $rule->action_type }}
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div>
                        <span class="font-semibold text-slate-500">A/B Testing Variants:</span>
                        <ul class="list-disc list-inside text-slate-700 mt-1 space-y-1">
                            @foreach($rule->reply_template_variants ?? [] as $variant)
                            <li class="bg-slate-50 p-1.5 rounded border border-slate-100">{{ $variant }}</li>
                            @endforeach
                        </ul>
                    </div>

                    @if(!empty($rule->business_hours_variant['reply_text']))
                    <div class="mt-2">
                        <span class="font-semibold text-slate-500">After-Hours Variant:</span>
                        <p class="bg-amber-50 text-amber-900 p-1.5 rounded border border-amber-100 mt-1">{{ $rule->business_hours_variant['reply_text'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl p-8 text-center text-xs text-slate-400 border border-slate-200">
                No automation rules configured yet.
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
