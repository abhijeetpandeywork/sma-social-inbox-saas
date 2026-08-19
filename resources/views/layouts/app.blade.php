<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Social Inbox Automation' }} — Digital Rubix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col">
    <!-- Top Navbar -->
    <header class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span class="font-bold text-lg tracking-wide text-white">Social Inbox <span class="text-amber-400">SaaS</span></span>
                </div>
                <nav class="hidden md:flex space-x-4 text-sm font-medium">
                    <a href="/dashboard" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-200">Unified Inbox & Kanban</a>
                    <a href="/automation" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-300">Automation Rules</a>
                    <a href="/reports/lead-quality" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-300">Lead Quality Report</a>
                    <a href="/health" target="_blank" class="px-3 py-2 rounded-md hover:bg-slate-800 text-slate-300 flex items-center gap-1">
                        System Health API
                        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                    </a>
                </nav>
            </div>

            <!-- Client Switcher & User Profile -->
            <div class="flex items-center space-x-4" x-data="{ clientOpen: false }">
                @if(isset($clients) && count($clients) > 0)
                <div class="relative">
                    <button @click="clientOpen = !clientOpen" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg text-sm flex items-center space-x-2 border border-slate-700">
                        <span class="text-xs text-amber-400 font-semibold uppercase tracking-wider">Client:</span>
                        <span class="font-medium">{{ $clients->firstWhere('id', $selectedClientId)->name ?? 'All Clients' }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="clientOpen" @click.away="clientOpen = false" x-cloak class="absolute right-0 mt-2 w-56 bg-slate-800 rounded-lg shadow-xl py-1 border border-slate-700 z-50">
                        @foreach($clients as $c)
                        <a href="?client_id={{ $c->id }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white flex items-center justify-between">
                            <span>{{ $c->name }}</span>
                            @if($c->id == $selectedClientId)<span class="w-2 h-2 rounded-full bg-amber-400"></span>@endif
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @auth
                <div class="text-xs text-slate-400 border-l border-slate-800 pl-4">
                    <div class="font-medium text-slate-200">{{ auth()->user()->name }}</div>
                    <div class="text-amber-400/90 text-[10px] uppercase font-semibold">{{ auth()->user()->role }}</div>
                </div>
                <form action="/logout" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-slate-400 hover:text-red-400 px-2 py-1">Logout</button>
                </form>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
