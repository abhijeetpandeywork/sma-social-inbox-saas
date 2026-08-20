<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function index()
    {
        $members = TeamMember::orderBy('role')->orderBy('name')->get();
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        return view('team.index', compact('members', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:team_members,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:Agency Admin,Client Manager,Team Executive',
            'assigned_clients' => 'nullable|array',
        ]);

        TeamMember::create([
            'agency_id' => auth()->user()->agency_id ?? 1,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'assigned_clients' => $validated['role'] === 'Agency Admin' ? [] : ($validated['assigned_clients'] ?? []),
            'two_factor_enabled' => $validated['role'] === 'Agency Admin',
            'two_factor_secret' => $validated['role'] === 'Agency Admin' ? '123456' : null,
        ]);

        return redirect()->route('team.index')->with('success', "Team member '{$validated['name']}' created successfully.");
    }

    public function destroy(TeamMember $member)
    {
        if ($member->id === auth()->id()) {
            return redirect()->route('team.index')->with('error', 'You cannot delete your own account while logged in.');
        }

        $name = $member->name;
        $member->delete();

        return redirect()->route('team.index')->with('success', "Team member '{$name}' removed.");
    }
}
