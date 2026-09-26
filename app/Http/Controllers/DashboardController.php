<?php

namespace App\Http\Controllers;

use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use App\Services\ClaudeClient;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ClaudeClient $claude): View
    {
        return view('dashboard', [
            'claudeConfigured' => $claude->isConfigured(),
            'commands' => MacAgentCommand::query()->with('device')->latest()->limit(20)->get(),
            'devices' => MacDevice::query()->orderBy('name')->get(),
        ]);
    }
}
