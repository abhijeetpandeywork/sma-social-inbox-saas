<x-app-layout>
    <div class="max-w-5xl mx-auto py-4" x-data="{ activeTab: 'meta' }">
        <div class="mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold uppercase tracking-wider mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Complete Knowledge Base
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900">Step-by-Step System Setup Guide</h1>
            <p class="text-xs text-slate-500">Everything you need to connect your social channels, configure webhooks, and automate your inbox</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-200 mb-6 gap-2 text-xs font-bold">
            <button @click="activeTab = 'meta'" :class="activeTab === 'meta' ? 'border-amber-500 text-amber-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 bg-slate-50'" class="py-2.5 px-4 rounded-t-lg border-b-2 transition-all flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-tr from-purple-500 to-pink-500"></span>
                1. Meta (Instagram & FB)
            </button>
            <button @click="activeTab = 'webhooks'" :class="activeTab === 'webhooks' ? 'border-amber-500 text-amber-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 bg-slate-50'" class="py-2.5 px-4 rounded-t-lg border-b-2 transition-all flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                2. Webhooks Setup
            </button>
            <button @click="activeTab = 'cron'" :class="activeTab === 'cron' ? 'border-amber-500 text-amber-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 bg-slate-50'" class="py-2.5 px-4 rounded-t-lg border-b-2 transition-all flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                3. Hostinger Cron Jobs
            </button>
            <button @click="activeTab = 'whatsapp'" :class="activeTab === 'whatsapp' ? 'border-amber-500 text-amber-600 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700 bg-slate-50'" class="py-2.5 px-4 rounded-t-lg border-b-2 transition-all flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                4. WhatsApp & Leads
            </button>
        </div>

        <!-- TAB 1: META SETUP -->
        <div x-show="activeTab === 'meta'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-purple-600 text-white flex items-center justify-center text-xs">IG</span>
                    How to Connect Your Instagram Business & Facebook Page
                </h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    To enable automatic comment replies, hiding sensitive phone numbers, and direct messaging, you need a free Meta Developer App.
                </p>

                <div class="space-y-3 pt-2">
                    <div class="flex gap-3 text-xs">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0">1</span>
                        <div>
                            <strong class="text-slate-800">Ensure Instagram is connected to a Facebook Page:</strong>
                            <p class="text-slate-500">Open your Instagram Mobile App &rarr; Settings &rarr; Account Type &rarr; Switch to Professional / Creator Account &rarr; Link to your business Facebook Page.</p>
                        </div>
                    </div>

                    <div class="flex gap-3 text-xs">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0">2</span>
                        <div>
                            <strong class="text-slate-800">Create a Meta Business App:</strong>
                            <p class="text-slate-500">Go to <a href="https://developers.facebook.com/apps" target="_blank" class="text-amber-600 underline font-semibold">developers.facebook.com/apps</a> &rarr; Click <strong>Create App</strong> &rarr; Select <strong>Other / Business</strong>.</p>
                        </div>
                    </div>

                    <div class="flex gap-3 text-xs">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0">3</span>
                        <div>
                            <strong class="text-slate-800">Generate a Never-Expiring Page Access Token:</strong>
                            <p class="text-slate-500">In Graph API Explorer (<a href="https://developers.facebook.com/tools/explorer" target="_blank" class="text-amber-600 underline">developers.facebook.com/tools/explorer</a>), select your App &rarr; Under User or Page, pick your Page &rarr; Add permissions <code class="bg-slate-100 text-slate-800 px-1 rounded">instagram_manage_comments</code>, <code class="bg-slate-100 text-slate-800 px-1 rounded">pages_manage_engagement</code>, <code class="bg-slate-100 text-slate-800 px-1 rounded">instagram_basic</code> &rarr; Generate Access Token.</p>
                        </div>
                    </div>

                    <div class="flex gap-3 text-xs">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 font-bold flex items-center justify-center shrink-0">4</span>
                        <div>
                            <strong class="text-slate-800">Paste in Social Inbox SaaS:</strong>
                            <p class="text-slate-500">Open <a href="{{ route('connections.index') }}" class="text-amber-600 font-bold underline">API Setup & Accounts</a> or use the <a href="{{ route('setup.wizard', ['step' => 3]) }}" class="text-amber-600 font-bold underline">Setup Wizard</a> to paste your token. All tokens are encrypted using AES-256 at rest.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: WEBHOOKS -->
        <div x-show="activeTab === 'webhooks'" class="space-y-6" x-cloak>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-blue-600 text-white flex items-center justify-center text-xs">WH</span>
                    Real-Time Meta Webhook Configuration
                </h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Webhooks notify our application within 1 second whenever a customer posts a comment or sends a DM on your Instagram or Facebook posts.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 bg-slate-900 text-white rounded-xl border border-slate-800">
                        <span class="text-[10px] font-bold uppercase text-amber-400">Callback URL:</span>
                        <div class="mt-1.5 p-2 bg-slate-950 rounded font-mono text-xs text-emerald-400 select-all border border-slate-800">
                            {{ $webhookUrl }}
                        </div>
                    </div>

                    <div class="p-4 bg-slate-900 text-white rounded-xl border border-slate-800">
                        <span class="text-[10px] font-bold uppercase text-amber-400">Verify Token:</span>
                        <div class="mt-1.5 p-2 bg-slate-950 rounded font-mono text-xs text-amber-400 select-all border border-slate-800">
                            {{ $verifyToken }}
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2 text-xs text-slate-700">
                    <p class="font-bold text-slate-900">Fields to Subscribe to in Meta Developer Portal:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-600">
                        <li><code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-amber-800 font-bold">comments</code> &mdash; Fires when anyone comments on an Instagram or Facebook post/reel.</li>
                        <li><code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-amber-800 font-bold">messages</code> &mdash; Fires on direct messages (DMs).</li>
                        <li><code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-amber-800 font-bold">feed</code> &mdash; Captures page-level timeline posts and interactions.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- TAB 3: CRON JOBS -->
        <div x-show="activeTab === 'cron'" class="space-y-6" x-cloak>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-emerald-600 text-white flex items-center justify-center text-xs">CR</span>
                    Hostinger Shared Hosting 1-Minute Cron Setup
                </h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Because Hostinger Shared Hosting does not permit persistent background worker daemons (like Redis supervisor), our system uses a database queue with a 1-minute cron job to process webhooks and check SLAs.
                </p>

                <div class="p-4 bg-slate-900 text-white rounded-xl border border-slate-800">
                    <span class="text-[10px] font-bold uppercase text-amber-400">Exact Cron Command to add in Hostinger hPanel:</span>
                    <div class="mt-2 p-3 bg-slate-950 rounded font-mono text-xs text-emerald-400 select-all border border-slate-800">
                        * * * * * /usr/bin/php /home/u406313474/domains/sma.digitalrubix.site/public_html/artisan schedule:run >> /dev/null 2>&1
                    </div>
                </div>

                <div class="text-xs text-slate-600 space-y-2">
                    <p><strong>Steps to configure in Hostinger:</strong></p>
                    <ol class="list-decimal list-inside space-y-1 text-slate-600">
                        <li>Log into Hostinger hPanel &rarr; Go to <strong>Advanced &rarr; Cron Jobs</strong>.</li>
                        <li>Under <strong>Type</strong>, select <strong>Custom</strong>.</li>
                        <li>Set Minute, Hour, Day, Month, Weekday to <code class="font-mono bg-slate-100 px-1 rounded">* * * * *</code> (every 1 minute).</li>
                        <li>Paste the command above into the Command box &rarr; Click <strong>Save</strong>.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- TAB 4: WHATSAPP & LEADS -->
        <div x-show="activeTab === 'whatsapp'" class="space-y-6" x-cloak>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-md bg-green-600 text-white flex items-center justify-center text-xs">WA</span>
                    1-Click WhatsApp Lead Handoff
                </h2>
                <p class="text-xs text-slate-600 leading-relaxed">
                    When a hot lead with a contact number is captured from Instagram or Facebook, our AI extracts the number and formats it with Indian country codes (+91) automatically.
                </p>

                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-900 space-y-2">
                    <p class="font-bold">How Your Sales Team Uses This:</p>
                    <p>On the Unified Kanban Board, each lead card displays an instant <strong>WhatsApp Chat</strong> button. Clicking this opens a pre-filled WhatsApp conversation on WhatsApp Web or Mobile without needing to save the contact number to a phone book.</p>
                </div>

                <div class="pt-2">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-5 py-2.5 rounded-lg text-xs shadow-sm transition-all">
                        Go to Unified Kanban Board &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
