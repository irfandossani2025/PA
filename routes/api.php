<?php

use App\Http\Controllers\Api\MacAgentHeartbeatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['mac-agent', 'throttle:mac-agent'])
    ->post('/mac-agent/heartbeat', [MacAgentHeartbeatController::class, 'store'])
    ->name('api.mac-agent.heartbeat');
