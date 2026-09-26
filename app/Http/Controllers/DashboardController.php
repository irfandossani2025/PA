<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversationMessage;
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
            'officeAgents' => [
                ['name' => 'PA Manager', 'role' => 'Coordinates work', 'state' => 'available'],
                ['name' => 'Executive Assistant', 'role' => 'Email, calendar & follow-ups', 'state' => 'available'],
                ['name' => 'Designer', 'role' => 'Branding & visual assets', 'state' => 'ready'],
                ['name' => 'Full-Stack Developer', 'role' => 'Websites & automation', 'state' => 'ready'],
                ['name' => 'UI/UX Designer', 'role' => 'User experience', 'state' => 'ready'],
                ['name' => 'SEO', 'role' => 'Search visibility', 'state' => 'ready'],
                ['name' => 'Marketing', 'role' => 'Lead generation', 'state' => 'ready'],
                ['name' => 'Sales', 'role' => 'Outreach & follow-up', 'state' => 'ready'],
                ['name' => 'Social Media', 'role' => 'Content & campaigns', 'state' => 'ready'],
            ],
            'messages' => AssistantConversationMessage::query()
                ->whereBelongsTo(request()->user())
                ->with('command.device')
                ->latest('id')
                ->limit(50)
                ->get()
                ->reverse()
                ->values(),
        ]);
    }
}
