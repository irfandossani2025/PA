<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversationMessage;
use App\Models\MacDevice;
use App\Services\AssistantTaskPlanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class AssistantChatController extends Controller
{
    public function __invoke(Request $request, AssistantTaskPlanner $taskPlanner): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = trim($validated['message']);

        AssistantConversationMessage::query()->create([
            'user_id' => $request->user()->id,
            'role' => 'user',
            'content' => $message,
        ]);

        try {
            $taskPlanner->plan(
                $request->user(),
                MacDevice::query()->orderBy('name')->first(),
                $message,
            );
        } catch (Throwable) {
            AssistantConversationMessage::query()->create([
                'user_id' => $request->user()->id,
                'role' => 'assistant',
                'content' => 'I could not prepare that right now. Please try again in a moment.',
            ]);
        }

        return to_route('dashboard');
    }
}
