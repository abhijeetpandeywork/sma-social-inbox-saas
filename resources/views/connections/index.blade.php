<x-app-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Platform Connections & API Setup Guide</h1>
        <p class="text-xs text-slate-500">Manage OAuth 2.0 access tokens, monitor token health, and follow step-by-step API integration guides</p>
    </div>

    <!-- Active Connections Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        @php
            $allPlatforms = [
                'instagram' => ['name' => 'Instagram Business', 'color' => 'from-purple-600 to-pink-500', 'icon' => 'IG'],
                'facebook' => ['name' => 'Facebook Page', 'color' => 'bg-blue-600', 'icon' => 'FB'],
                'twitter' => ['name' => 'Twitter / X', 'color' => 'bg-slate-900', 'icon' => 'X'],
                'youtube' => ['name' => 'YouTube Comments', 'color' => 'bg-red-600', 'icon' => 'YT'],
                'gmb' => ['name' => 'Google Business Reviews', 'color' => 'bg-emerald-600', 'icon' => 'GMB'],
                'linkedin' => ['name' => 'LinkedIn Alerts', 'color' => 'bg-sky-700', 'icon' => 'IN'],
            ];
        @endphp

        @foreach($allPlatforms as $pKey => $pMeta)
            @php
                $conn = $connections->firstWhere('platform', $pKey);
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-8 h-8 rounded-lg bg-gradient-to-r {{ $pMeta['color'] }} text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ $pMeta['icon'] }}
                        </span>
                        <div>
                            <h3 class="font-bold text-xs text-slate-800">{{ $pMeta['name'] }}</h3>
                            <span class="text-[10px] text-slate-400">Account ID: {{ $conn ? $conn->platform_account_id : 'Not Connected' }}</span>
                        </div>
                    </div>

                    @if($conn)
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Healthy</span>
                    @else
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-500">Not Connected</span>
                    @endif
                </div>

                @if($conn)
                <div class="text-[11px] text-slate-600 space-y-1 pt-2 border-t border-slate-100">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Token Expires:</span>
                        <span class="font-semibold text-slate-700">{{ $conn->token_expires_at ? $conn->token_expires_at->diffForHumans() : 'Never' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Last Verified Call:</span>
                        <span class="font-semibold text-slate-700">{{ $conn->last_successful_call_at ? $conn->last_successful_call_at->diffForHumans() : 'N/A' }}</span>
                    </div>
                </div>
                @else
                <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-100 italic">
                    Add access token below to activate automation.
                </p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Token Configuration Form -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Configure Platform Token</h2>

            <form action="/connections" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Client</label>
                    <select name="client_id" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $selectedClientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
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
                        <option value="gmb">Google Business Profile</option>
                        <option value="linkedin">LinkedIn</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Platform Account / Page ID</label>
                    <input type="text" name="platform_account_id" required placeholder="e.g. 17841400000000" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Access Token (AES-256 Encrypted at Rest)</label>
                    <textarea name="access_token" rows="3" required placeholder="Paste Page/User Access Token here..." class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800 font-mono"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Validity (Days)</label>
                    <input type="number" name="token_expires_in_days" value="60" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2 text-slate-800">
                </div>

                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-xs transition-all shadow-sm">
                    Save Encrypted Token
                </button>
            </form>
        </div>

        <!-- Step-by-Step Meta & Platform Setup Guide -->
        <div class="lg:col-span-2 bg-slate-900 text-slate-100 rounded-xl p-6 shadow-sm border border-slate-800">
            <h2 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                Step-by-Step Webhook & API Setup Guide
            </h2>
            <p class="text-xs text-slate-400 mb-6">How to connect Instagram, Facebook, and external platform Webhooks to this SaaS</p>

            <div class="space-y-6 text-xs">
                <!-- Meta Setup Block -->
                <div class="bg-slate-800/80 p-4 rounded-lg border border-slate-700 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                        <span class="font-bold text-amber-400 text-sm">1. Meta (Instagram + Facebook) Webhook Setup</span>
                        <span class="bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded text-[10px] font-semibold">Graph API v19.0</span>
                    </div>

                    <div class="space-y-2 text-slate-300">
                        <p><strong>Callback URL:</strong></p>
                        <div class="bg-slate-950 p-2 rounded font-mono text-emerald-400 select-all border border-slate-800">
                            {{ $webhookUrl }}
                        </div>

                        <p><strong>Verify Token:</strong></p>
                        <div class="bg-slate-950 p-2 rounded font-mono text-amber-400 select-all border border-slate-800">
                            {{ $verifyToken }}
                        </div>

                        <ol class="list-decimal list-inside space-y-1.5 pt-2 text-slate-300">
                            <li>Go to <a href="https://developers.facebook.com" target="_blank" class="text-amber-400 underline">developers.facebook.com</a> &rarr; Select your Meta Business App.</li>
                            <li>Add <strong>Webhooks</strong> product &rarr; Select <strong>Instagram</strong> or <strong>Page</strong> object.</li>
                            <li>Click <strong>Subscribe to this object</strong> &rarr; Paste the Callback URL and Verify Token above.</li>
                            <li>Subscribe to fields: <code class="text-amber-300">comments</code>, <code class="text-amber-300">messages</code>, <code class="text-amber-300">feed</code>.</li>
                            <li>In App Review, request permissions: <code class="text-amber-300">instagram_manage_comments</code>, <code class="text-amber-300">pages_manage_engagement</code>.</li>
                        </ol>
                    </div>
                </div>

                <!-- Twitter / X Setup Block -->
                <div class="bg-slate-800/80 p-4 rounded-lg border border-slate-700 space-y-2">
                    <div class="font-bold text-sky-400 text-sm border-b border-slate-700 pb-2">2. Twitter / X Pay-Per-Use API Setup</div>
                    <p class="text-slate-300">Go to <a href="https://developer.x.com" target="_blank" class="text-amber-400 underline">developer.x.com</a> &rarr; Create App with OAuth 2.0 PKCE permissions. Usage costs ($0.005 read / $0.015 write DM) are automatically tracked in client audit logs.</p>
                </div>

                <!-- YouTube Setup Block -->
                <div class="bg-slate-800/80 p-4 rounded-lg border border-slate-700 space-y-2">
                    <div class="font-bold text-red-400 text-sm border-b border-slate-700 pb-2">3. YouTube Data API v3 Setup</div>
                    <p class="text-slate-300">Go to <a href="https://console.cloud.google.com" target="_blank" class="text-amber-400 underline">console.cloud.google.com</a> &rarr; Enable YouTube Data API v3 &rarr; Create OAuth Consent credentials &rarr; Add Refresh Token above.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
