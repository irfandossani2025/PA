<?php

use App\Http\Controllers\AssistantChatController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MacAgentCommandController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware(['auth', 'owner'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/chat', AssistantChatController::class)->middleware('throttle:assistant-chat')->name('chat.store');
    Route::post('/commands', [MacAgentCommandController::class, 'store'])->name('commands.store');
    Route::post('/commands/{command}/approve', [MacAgentCommandController::class, 'approve'])->name('commands.approve');
    Route::post('/commands/{command}/cancel', [MacAgentCommandController::class, 'cancel'])->name('commands.cancel');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
