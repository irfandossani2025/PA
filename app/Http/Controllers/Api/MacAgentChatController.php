<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistantConversationMessage;
use App\Models\User;
use App\Services\ClaudeClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MacAgentChatController extends Controller
{
    public function store(Request $request, ClaudeClient $claude): JsonResponse
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
            $reply = str($claude->respond($message, 'You are IRFAN PA Manager. Reply concisely in the user language. State what you will do, but do not claim an unverified task has completed.'))->trim()->limit(1000)->toString();
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
