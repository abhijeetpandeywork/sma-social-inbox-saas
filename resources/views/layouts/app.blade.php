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
            <div class="flex items-center space-x-5">
                <a href="/dashboard" class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 shadow-sm"></span>
                    <span class="font-bold text-base sm:text-lg tracking-wide text-white">Social Inbox <span class="text-amber-400">SaaS</span></span>
                </a>

                <nav class="hidden lg:flex space-x-1 text-xs font-medium">
                    <a href="/dashboard" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('dashboard') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">Inbox</a>
                    <a href="/setup" class="px-2.5 py-1.5 rounded-md bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        Setup Wizard
                    </a>
                    <a href="/clients" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('clients*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">Businesses</a>
                    <a href="/team" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('team*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">Team</a>
                    <a href="/automation" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('automation*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">Rules</a>
                    <a href="/connections" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('connections*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">API Accounts</a>
                    <a href="/guide" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('guide*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">Setup Guide</a>
                    <a href="/reports/lead-quality" class="px-2.5 py-1.5 rounded-md hover:bg-slate-800 {{ request()->is('reports*') ? 'bg-slate-800 text-amber-400 font-bold' : 'text-slate-300' }}">Reports</a>
                </nav>
            </div>

            <!-- Client Switcher, Data Reset & User Profile -->
            <div class="flex items-center space-x-3" x-data="{ clientOpen: false, userMenuOpen: false }">
                @if(isset($clients) && count($clients) > 0)
                <div class="relative">
                    <button @click="clientOpen = !clientOpen" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg text-xs flex items-center space-x-1.5 border border-slate-700">
                        <span class="text-[10px] text-amber-400 font-bold uppercase tracking-wider">Client:</span>
                        <span class="font-medium max-w-[110px] truncate">{{ $clients->firstWhere('id', $selectedClientId)->name ?? 'All Clients' }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="clientOpen" @click.away="clientOpen = false" x-cloak class="absolute right-0 mt-2 w-56 bg-slate-800 rounded-lg shadow-xl py-1 border border-slate-700 z-50">
                        <a href="?client_id=" class="block px-4 py-2 text-xs text-slate-300 hover:bg-slate-700 hover:text-white flex items-center justify-between">
                            <span>All Clients</span>
                            @if(!$selectedClientId)<span class="w-2 h-2 rounded-full bg-amber-400"></span>@endif
                        </a>
                        @foreach($clients as $c)
                        <a href="?client_id={{ $c->id }}" class="block px-4 py-2 text-xs text-slate-300 hover:bg-slate-700 hover:text-white flex items-center justify-between">
                            <span class="truncate">{{ $c->name }}</span>
                            @if($c->id == $selectedClientId)<span class="w-2 h-2 rounded-full bg-amber-400"></span>@endif
                        </a>
                        @endforeach
                        <div class="border-t border-slate-700 mt-1 pt-1">
                            <a href="/clients" class="block px-4 py-2 text-xs text-amber-400 hover:bg-slate-700 font-bold">
                                + Manage Businesses
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                @auth
                <!-- User Profile & Reset Menu -->
                <div class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 text-xs text-slate-300 bg-slate-800 hover:bg-slate-700 px-2.5 py-1.5 rounded-lg border border-slate-700">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-bold flex items-center justify-center text-[10px]">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                        <span class="hidden sm:inline font-medium">{{ auth()->user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="userMenuOpen" @click.away="userMenuOpen = false" x-cloak class="absolute right-0 mt-2 w-56 bg-slate-800 rounded-lg shadow-xl py-2 border border-slate-700 z-50 text-xs">
                        <div class="px-4 py-1.5 border-b border-slate-700 text-slate-400">
                            <p class="font-bold text-slate-200">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-amber-400 uppercase font-semibold">{{ auth()->user()->role }}</p>
                        </div>

                        <a href="/setup" class="block px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white">
                            Quick Setup Wizard
                        </a>
                        <a href="/guide" class="block px-4 py-2 text-slate-300 hover:bg-slate-700 hover:text-white">
                            Setup Guides & API Docs
                        </a>

                        @if(auth()->user()->role === 'Agency Admin')
                            <form action="{{ route('setup.reset') }}" method="POST" onsubmit="return confirm('WARNING: This will wipe all mock leads, mock connections, and sample rules to give you a 100% clean slate. Continue?');" class="border-t border-slate-700 pt-1 mt-1">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-red-400 hover:bg-slate-700 hover:text-red-300 font-semibold flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Reset to Clean Slate
                                </button>
                            </form>
                        @endif

                        <form action="/logout" method="POST" class="border-t border-slate-700 pt-1 mt-1">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-slate-400 hover:bg-slate-700 hover:text-red-400 font-medium">
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile Navigation Bar -->
        <div class="lg:hidden bg-slate-950 px-4 py-2 flex overflow-x-auto space-x-2 text-xs border-t border-slate-800">
            <a href="/dashboard" class="px-2 py-1 rounded text-slate-300 whitespace-nowrap">Inbox</a>
            <a href="/setup" class="px-2 py-1 rounded bg-amber-500/20 text-amber-400 font-bold whitespace-nowrap">Setup Wizard</a>
            <a href="/clients" class="px-2 py-1 rounded text-slate-300 whitespace-nowrap">Businesses</a>
            <a href="/team" class="px-2 py-1 rounded text-slate-300 whitespace-nowrap">Team</a>
            <a href="/automation" class="px-2 py-1 rounded text-slate-300 whitespace-nowrap">Rules</a>
            <a href="/connections" class="px-2 py-1 rounded text-slate-300 whitespace-nowrap">APIs</a>
            <a href="/guide" class="px-2 py-1 rounded text-slate-300 whitespace-nowrap">Guide</a>
        </div>
    </header>

    <!-- Flash Messages -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-800 px-4 py-3 rounded-xl text-xs font-semibold flex items-center justify-between shadow-xs">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </span>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
