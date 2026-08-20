<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with(['platformConnections', 'automationRules'])->orderBy('name')->get();
        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,paused',
            'data_retention_months' => 'required|integer|min:1|max:60',
        ]);

        $client = Client::create([
            'agency_id' => auth()->user()->agency_id ?? 1,
            'name' => $validated['name'],
            'status' => $validated['status'],
            'data_retention_months' => $validated['data_retention_months'],
        ]);

        return redirect()->route('clients.index')->with('success', "Business '{$client->name}' added successfully! You can now connect its social platforms.");
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,paused',
            'data_retention_months' => 'required|integer|min:1|max:60',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', "Business '{$client->name}' updated successfully.");
    }

    public function destroy(Client $client)
    {
        $name = $client->name;
        $client->delete();

        return redirect()->route('clients.index')->with('success', "Business '{$name}' has been deleted.");
    }
}
