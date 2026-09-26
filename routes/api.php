<?php

use App\Http\Controllers\Api\MacAgentCommandCompletionController;
use App\Http\Controllers\Api\MacAgentHeartbeatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['mac-agent', 'throttle:mac-agent'])
    ->post('/mac-agent/heartbeat', [MacAgentHeartbeatController::class, 'store'])
    ->name('api.mac-agent.heartbeat');

Route::middleware(['mac-agent', 'throttle:mac-agent'])
    ->post('/mac-agent/commands/{command}/complete', [MacAgentCommandCompletionController::class, 'store'])
    ->whereNumber('command')
    ->name('api.mac-agent.commands.complete');
