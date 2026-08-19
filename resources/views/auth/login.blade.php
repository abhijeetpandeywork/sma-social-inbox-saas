<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Social Inbox SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-xl shadow-2xl border border-slate-700 p-8">
        <div class="text-center mb-6">
            <span class="w-4 h-4 rounded-full bg-amber-500 inline-block mb-2"></span>
            <h1 class="text-2xl font-bold text-white">Social Inbox SaaS</h1>
            <p class="text-xs text-slate-400 mt-1">Digital Rubix Multi-Tenant Agency Console</p>
        </div>

        @if($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded-lg text-xs">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="/login" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full text-sm bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:border-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required class="w-full text-sm bg-slate-900 border border-slate-700 rounded-lg p-2.5 text-white focus:outline-none focus:border-amber-500">
            </div>

            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition-all shadow-md mt-2">
                Sign In to Console
            </button>
        </form>
    </div>
</body>
</html>
