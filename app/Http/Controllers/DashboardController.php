<?php

namespace App\Http\Controllers;

use App\Services\ClaudeClient;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ClaudeClient $claude): View
    {
        return view('dashboard', [
            'claudeConfigured' => $claude->isConfigured(),
        ]);
    }
}
