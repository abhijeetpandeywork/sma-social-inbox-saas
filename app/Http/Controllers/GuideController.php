<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function index()
    {
        $webhookUrl = url('/api/webhooks/meta');
        $verifyToken = config('services.meta.verify_token', 'social_inbox_secret_token');
        $clients = Client::where('status', 'active')->orderBy('name')->get();

        return view('guide.index', compact('webhookUrl', 'verifyToken', 'clients'));
    }
}
