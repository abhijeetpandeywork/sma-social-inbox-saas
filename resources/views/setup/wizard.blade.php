<x-app-layout>
    <div class="max-w-4xl mx-auto py-4">
        <!-- Wizard Header -->
        <div class="text-center mb-8">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Quick Setup Wizard
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Configure Your Social Automation in 5 Simple Steps</h1>
            <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto mt-1.5">Follow this step-by-step guide to connect your Instagram, configure auto-replies, and start capturing customer leads automatically.</p>
        </div>

        <!-- 5-Step Stepper Bar -->
        <div class="mb-8">
            <div class="grid grid-cols-5 gap-2 sm:gap-4 text-center">
                @php
                    $steps = [
                        1 => ['title' => 'Profile', 'desc' => 'Agency Admin'],
                        2 => ['title' => 'Business', 'desc' => 'Add Brand'],
                        3 => ['title' => 'Connect', 'desc' => 'Meta / Social'],
                        4 => ['title' => 'Automate', 'desc' => 'Auto-Reply'],
                        5 => ['title' => 'Verify', 'desc' => 'Test Lead'],
                    ];
                @endphp

                @foreach($steps as $sNum => $sInfo)
                    <a href="{{ route('setup.wizard', ['step' => $sNum]) }}" class="group block">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-full font-bold text-xs sm:text-sm transition-all
                            {{ $step == $sNum ? 'bg-amber-500 text-slate-950 ring-4 ring-amber-100 shadow-md scale-105' : ($step > $sNum ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500') }}">
                            @if($step > $sNum)
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                {{ $sNum }}
                            @endif
                        </div>
                        <div class="mt-2">
                            <p class="text-[11px] sm:text-xs font-bold {{ $step == $sNum ? 'text-slate-900' : 'text-slate-500' }}">{{ $sInfo['title'] }}</p>
                            <p class="text-[9px] sm:text-[10px] text-slate-400 hidden sm:block">{{ $sInfo['desc'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="relative mt-2">
                <div class="h-1 bg-slate-200 rounded-full">
                    <div class="h-1 bg-amber-500 rounded-full transition-all duration-300" style="width: {{ (($step - 1) / 4) * 100 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Step Container Cards -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">

            <!-- STEP 1: Agency & Profile -->
            @if($step == 1)
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">1</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Step 1: Set Up Administrator Account</h2>
                            <p class="text-xs text-slate-500">Your primary administrator credentials for accessing the platform</p>
                        </div>
                    </div>

                    <form action="{{ route('setup.wizard.step1') }}" method="POST" class="space-y-4 max-w-lg">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Administrator Full Name</label>
                            <input type="text" name="admin_name" value="{{ old('admin_name', $admin->name ?? 'Agency Administrator') }}" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Admin Email Address (Login ID)</label>
                            <input type="email" name="admin_email" value="{{ old('admin_email', $admin->email ?? 'admin@digitalrubix.com') }}" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">New Password (Leave blank to keep current)</label>
                            <input type="password" name="admin_password" placeholder="••••••••" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        </div>

                        <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-xs text-amber-800">
                            <strong>Note on 2FA:</strong> Two-Factor Authentication is active on Agency Admin accounts. Default TOTP secret code is <code class="bg-amber-200/70 px-1.5 py-0.5 rounded font-mono font-bold">123456</code>.
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-xs shadow-sm flex items-center gap-2">
                                Next: Add Business &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- STEP 2: Add First Client / Business -->
            @if($step == 2)
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">2</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Step 2: Add Your Business or Client Brand</h2>
                            <p class="text-xs text-slate-500">Create the brand profile that will be linked to your social media accounts</p>
                        </div>
                    </div>

                    @if($clients->count() > 0)
                        <div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-xs font-bold text-slate-700 mb-2">Existing Businesses Already Configured:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($clients as $c)
                                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-300 px-3 py-1 rounded-lg text-xs font-semibold text-slate-800">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        {{ $c->name }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('setup.wizard', ['step' => 3, 'client_id' => $firstClient->id]) }}" class="text-xs text-amber-600 font-bold hover:underline">
                                    Continue with existing business &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('setup.wizard.step2') }}" method="POST" class="space-y-4 max-w-lg">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Business / Brand Name *</label>
                            <input type="text" name="business_name" required placeholder="e.g. My Awesome Brand, Sai Business" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                            <p class="text-[11px] text-slate-400 mt-1">This will identify which brand's social pages are being processed.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Data Retention (Months)</label>
                            <input type="number" name="data_retention_months" value="12" min="1" max="60" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                            <p class="text-[11px] text-slate-400 mt-1">How long to keep interaction records (standard: 12 months).</p>
                        </div>

                        <div class="pt-4 flex justify-between">
                            <a href="{{ route('setup.wizard', ['step' => 1]) }}" class="text-xs text-slate-500 hover:text-slate-800 py-2.5 font-semibold">
                                &larr; Back
                            </a>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-xs shadow-sm flex items-center gap-2">
                                Next: Connect Social Accounts &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- STEP 3: Connect Social Accounts -->
            @if($step == 3)
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">3</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Step 3: Connect Instagram & Facebook Pages</h2>
                            <p class="text-xs text-slate-500">Provide the Page ID and Access Token to allow automated commenting and DM responses</p>
                        </div>
                    </div>

                    @if($activeConnections->count() > 0)
                        <div class="mb-6 p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                            <p class="text-xs font-bold text-emerald-800 mb-2">Connected Accounts for this Brand:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($activeConnections as $conn)
                                    <span class="inline-flex items-center gap-1.5 bg-white border border-emerald-300 px-3 py-1 rounded-lg text-xs font-semibold text-emerald-800">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        {{ strtoupper($conn->platform) }} (ID: {{ $conn->platform_account_id }})
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('setup.wizard.step3') }}" method="POST" class="space-y-4 max-w-xl">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Select Business *</label>
                            <select name="client_id" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ (request('client_id') == $c->id || ($firstClient && $firstClient->id == $c->id)) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Social Platform *</label>
                            <select name="platform" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                                <option value="instagram">Instagram Business</option>
                                <option value="facebook">Facebook Page</option>
                                <option value="twitter">Twitter / X</option>
                                <option value="youtube">YouTube</option>
                                <option value="gmb">Google Business Profile</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Platform Account / Page ID *</label>
                            <input type="text" name="platform_account_id" required placeholder="e.g. 17841400000000 (Instagram Account ID or FB Page ID)" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800 font-mono">
                            <p class="text-[11px] text-slate-400 mt-1">Found in your Meta Business Suite &rarr; Page Settings &rarr; Page ID.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Page Access Token (AES-256 Encrypted) *</label>
                            <textarea name="access_token" rows="3" required placeholder="Paste Page Access Token from developers.facebook.com..." class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800 font-mono"></textarea>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Need help getting your token? Check our <a href="{{ route('guide.index') }}" target="_blank" class="text-amber-600 font-bold underline">Visual Token Guide &rarr;</a>
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Token Validity (Days)</label>
                            <input type="number" name="token_expires_in_days" value="60" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        </div>

                        <div class="pt-4 flex justify-between">
                            <a href="{{ route('setup.wizard', ['step' => 2]) }}" class="text-xs text-slate-500 hover:text-slate-800 py-2.5 font-semibold">
                                &larr; Back
                            </a>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-xs shadow-sm flex items-center gap-2">
                                Next: Auto-Replies & WhatsApp &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- STEP 4: Auto-Reply & WhatsApp Templates -->
            @if($step == 4)
                <div>
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">4</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Step 4: Configure Auto-Replies & WhatsApp Handoff</h2>
                            <p class="text-xs text-slate-500">Set the messages sent when a customer asks for price, details, or leaves a phone number</p>
                        </div>
                    </div>

                    <form action="{{ route('setup.wizard.step4') }}" method="POST" class="space-y-4 max-w-xl">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Select Business *</label>
                            <select name="client_id" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ (request('client_id') == $c->id || ($firstClient && $firstClient->id == $c->id)) ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Platform *</label>
                            <select name="platform" required class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                                <option value="instagram">Instagram</option>
                                <option value="facebook">Facebook</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Auto-Reply Message (Variant 1 - Primary) *</label>
                            <input type="text" name="reply_variant_1" required value="Hi! We have sent full price and catalog details to your DM. Please check your inbox!" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                            <p class="text-[11px] text-slate-400 mt-1">This comment is published under the customer's comment automatically.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Auto-Reply Message (Variant 2 - A/B Testing, Optional)</label>
                            <input type="text" name="reply_variant_2" value="Thanks for reaching out! Check your direct messages for complete details." class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">After-Hours / Night Response (Optional)</label>
                            <input type="text" name="business_hours_reply" value="Thank you! Our team is currently offline, but we will call or WhatsApp you first thing tomorrow at 9 AM." class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                            <p class="text-[11px] text-slate-400 mt-1">Sent outside 9 AM - 8 PM IST.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Action on Customer Comments with Phone Numbers *</label>
                            <select name="action_type" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                                <option value="reply_and_hide">Reply to comment AND Hide phone number (Protects customer privacy)</option>
                                <option value="reply_only">Reply only (Do not hide)</option>
                            </select>
                        </div>

                        <div class="pt-4 flex justify-between">
                            <a href="{{ route('setup.wizard', ['step' => 3]) }}" class="text-xs text-slate-500 hover:text-slate-800 py-2.5 font-semibold">
                                &larr; Back
                            </a>
                            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-6 py-2.5 rounded-lg text-xs shadow-sm flex items-center gap-2">
                                Next: Webhook Verification & Testing &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- STEP 5: Webhook & Live Test Simulation -->
            @if($step == 5)
                <div x-data="{ copiedUrl: false, copiedToken: false }">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">5</div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Step 5: Verify Webhooks & Complete Setup</h2>
                            <p class="text-xs text-slate-500">Copy your webhook credentials to Meta and run a live test lead</p>
                        </div>
                    </div>

                    <!-- Webhook Boxes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-slate-900 text-white rounded-xl border border-slate-800">
                            <span class="text-[10px] font-bold uppercase text-amber-400">Meta Webhook Callback URL:</span>
                            <div class="mt-1.5 p-2 bg-slate-950 rounded font-mono text-xs text-emerald-400 flex items-center justify-between border border-slate-800">
                                <span class="truncate">{{ $webhookUrl }}</span>
                                <button @click="navigator.clipboard.writeText('{{ $webhookUrl }}'); copiedUrl = true; setTimeout(() => copiedUrl = false, 2000)" class="text-xs text-amber-400 hover:text-amber-300 font-bold ml-2 shrink-0">
                                    <span x-show="!copiedUrl">Copy</span>
                                    <span x-show="copiedUrl" class="text-emerald-400 font-bold">Copied!</span>
                                </button>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-900 text-white rounded-xl border border-slate-800">
                            <span class="text-[10px] font-bold uppercase text-amber-400">Verify Token:</span>
                            <div class="mt-1.5 p-2 bg-slate-950 rounded font-mono text-xs text-amber-400 flex items-center justify-between border border-slate-800">
                                <span class="truncate">{{ $verifyToken }}</span>
                                <button @click="navigator.clipboard.writeText('{{ $verifyToken }}'); copiedToken = true; setTimeout(() => copiedToken = false, 2000)" class="text-xs text-amber-400 hover:text-amber-300 font-bold ml-2 shrink-0">
                                    <span x-show="!copiedToken">Copy</span>
                                    <span x-show="copiedToken" class="text-emerald-400 font-bold">Copied!</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Test Lead Generator Section -->
                    <div class="p-5 bg-amber-500/10 border border-amber-500/30 rounded-xl mb-6">
                        <h3 class="text-sm font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Instant Pipeline Test (Simulate an Incoming Lead)
                        </h3>
                        <p class="text-xs text-slate-600 mb-4">Click below to simulate an incoming customer comment with a phone number. This verifies your AI score, intent detection, and Kanban cards.</p>

                        @if($clients->count() > 0)
                            <form action="{{ route('leads.simulate') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @csrf
                                <input type="hidden" name="client_id" value="{{ $firstClient->id ?? $clients->first()->id }}">
                                <input type="hidden" name="platform" value="instagram">
                                <input type="hidden" name="contact_name" value="Amit Patel (Test)">
                                <input type="hidden" name="contact_phone" value="+919876543210">
                                <input type="hidden" name="comment_text" value="Bhai mujhe ye buy karna hai urgent, please call me at +919876543210">

                                <button type="submit" class="col-span-full sm:col-span-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-4 py-2.5 rounded-lg text-xs shadow-sm flex items-center justify-center gap-1.5 transition-all">
                                    Simulate HOT Lead Now &rarr;
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="pt-4 flex justify-between items-center border-t border-slate-100">
                        <a href="{{ route('setup.wizard', ['step' => 4]) }}" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">
                            &larr; Back
                        </a>
                        <a href="{{ route('dashboard') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-8 py-3 rounded-xl text-xs shadow-sm flex items-center gap-2 transition-all">
                            Go to Unified Lead Inbox &rarr;
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
