<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistantConversationMessage;
use App\Models\MacDevice;
use App\Models\User;
use App\Services\AssistantTaskPlanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MacAgentChatController extends Controller
{
    public function store(Request $request, AssistantTaskPlanner $taskPlanner): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $owner = User::query()->where('is_owner', true)->firstOrFail();
        $message = trim($validated['message']);

        AssistantConversationMessage::query()->create([
            'user_id' => $owner->id,
            'role' => 'user',
            'content' => $message,
        ]);

        try {
            /** @var MacDevice $device */
            $device = $request->attributes->get('mac_device');
            $reply = $taskPlanner->plan($owner, $device, $message)['reply'];
        } catch (Throwable) {
            $reply = 'I could not prepare that right now. Please try again in a moment.';
        }

        AssistantConversationMessage::query()->create([
            'user_id' => $owner->id,
            'role' => 'assistant',
            'content' => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    }
}
