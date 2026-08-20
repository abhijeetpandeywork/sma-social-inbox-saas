<x-app-layout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Team & Staff Access (RBAC)</h1>
            <p class="text-xs text-slate-500">Manage user accounts, assign team executives to specific client brands, and configure roles</p>
        </div>
        <button @click="$dispatch('open-add-member-modal')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs px-4 py-2 rounded-lg shadow-sm flex items-center gap-1.5 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            + Invite Team Member
        </button>
    </div>

    <!-- Team Members Table -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden mb-8">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <th class="py-3 px-4">Member</th>
                    <th class="py-3 px-4">Role</th>
                    <th class="py-3 px-4">Assigned Brands</th>
                    <th class="py-3 px-4">2FA Status</th>
                    <th class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
                @foreach($members as $m)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $m->name }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $m->email }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $m->role === 'Agency Admin' ? 'bg-purple-100 text-purple-700' : ($m->role === 'Client Manager' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $m->role }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            @if($m->role === 'Agency Admin')
                                <span class="text-amber-600 font-semibold">Global (All Clients)</span>
                            @elseif(empty($m->assigned_clients))
                                <span class="text-slate-400 italic">None assigned</span>
                            @else
                                @php
                                    $assignedNames = $clients->whereIn('id', $m->assigned_clients)->pluck('name')->join(', ');
                                @endphp
                                <span class="font-medium">{{ $assignedNames ?: 'None' }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if($m->two_factor_enabled)
                                <span class="text-emerald-600 font-bold text-[11px] flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Enabled (2FA)
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">Disabled</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($m->id !== auth()->id())
                                <form action="{{ route('team.destroy', $m->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove team member {{ $m->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold">Delete</button>
                                </form>
                            @else
                                <span class="text-[10px] text-slate-400 italic">Current User</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Add Member Modal -->
    <div x-data="{ open: false, role: 'Team Executive' }" @open-add-member-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="open = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Invite Team Member</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('team.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Sarah Jenkins" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" required placeholder="sarah@company.com" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Initial Password *</label>
                    <input type="password" name="password" required placeholder="Min 6 characters" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Role *</label>
                    <select name="role" x-model="role" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-slate-800">
                        <option value="Team Executive">Team Executive (Handles Leads)</option>
                        <option value="Client Manager">Client Manager (Oversees Specific Clients)</option>
                        <option value="Agency Admin">Agency Admin (Full System Access + 2FA)</option>
                    </select>
                </div>

                <div x-show="role !== 'Agency Admin'">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Assign to Client Brands</label>
                    <div class="max-h-32 overflow-y-auto space-y-1 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                        @foreach($clients as $c)
                            <label class="flex items-center space-x-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="assigned_clients[]" value="{{ $c->id }}" class="rounded text-amber-500 focus:ring-amber-400">
                                <span>{{ $c->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex gap-3">
                    <button type="button" @click="open = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2 rounded-lg text-xs transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2 rounded-lg text-xs shadow-sm transition-all">
                        Create Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
