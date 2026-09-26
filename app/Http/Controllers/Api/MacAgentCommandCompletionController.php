<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistantConversationMessage;
use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use App\Models\User;
use App\Services\OutlookInboxSummarizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MacAgentCommandCompletionController extends Controller
{
    public function store(Request $request, int $command, OutlookInboxSummarizer $outlookInboxSummarizer): JsonResponse
    {
        $validated = $request->validate([
            'success' => ['required', 'boolean'],
            'result' => ['nullable', 'array'],
            'result.message' => ['nullable', 'string', 'max:500'],
            'result.outlook_text' => ['nullable', 'string', 'max:12000'],
        ]);

        /** @var MacDevice $device */
        $device = $request->attributes->get('mac_device');

        /** @var MacAgentCommand $macAgentCommand */
        $macAgentCommand = $device->commands()
            ->whereKey($command)
            ->where('status', 'dispatched')
            ->firstOrFail();

        $macAgentCommand->forceFill([
            'status' => $validated['success'] ? 'completed' : 'failed',
            'result' => Arr::only($validated['result'] ?? [], ['message']),
            'completed_at' => now(),
        ])->save();

        if ($validated['success'] && $macAgentCommand->action === 'inspect_outlook_inbox' && filled($validated['result']['outlook_text'] ?? null)) {
            $owner = User::query()->where('is_owner', true)->first();

            if ($owner !== null) {
                try {
                    AssistantConversationMessage::query()->create([
                        'user_id' => $owner->id,
                        'role' => 'assistant',
                        'content' => $outlookInboxSummarizer->summarize($validated['result']['outlook_text']),
                        'mac_agent_command_id' => $macAgentCommand->id,
                    ]);
                } catch (\Throwable) {
                    AssistantConversationMessage::query()->create([
                        'user_id' => $owner->id,
                        'role' => 'assistant',
                        'content' => 'I read the visible Outlook Inbox, but could not summarize it right now. Please try again shortly.',
                        'mac_agent_command_id' => $macAgentCommand->id,
                    ]);
                }
            }
        }

        return response()->json([
            'command' => [
                'id' => $macAgentCommand->id,
                'status' => $macAgentCommand->status,
            ],
        ]);
    }
}
