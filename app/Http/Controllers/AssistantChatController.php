<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversationMessage;
use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use App\Services\ClaudeClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JsonException;
use Throwable;

class AssistantChatController extends Controller
{
    public function __invoke(Request $request, ClaudeClient $claude): RedirectResponse
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
            $assistantResponse = $claude->respond($message, $this->systemPrompt());
            $response = $this->decodeResponse($assistantResponse);
            $command = $this->createAutonomousTask($response['command'] ?? null);

            AssistantConversationMessage::query()->create([
                'user_id' => $request->user()->id,
                'role' => 'assistant',
                'content' => $response['reply'],
                'mac_agent_command_id' => $command?->id,
            ]);
        } catch (Throwable) {
            AssistantConversationMessage::query()->create([
                'user_id' => $request->user()->id,
                'role' => 'assistant',
                'content' => 'I could not prepare that right now. Please try again in a moment.',
            ]);
        }

        return to_route('dashboard');
    }

    /**
     * @return array{reply: string, command: array<string, mixed>|null}
     */
    private function decodeResponse(string $response): array
    {
        try {
            $decoded = json_decode(trim($response), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ['reply' => trim($response), 'command' => null];
        }

        if (! is_array($decoded) || ! is_string($decoded['reply'] ?? null)) {
            return ['reply' => 'I could not understand that request. Please try again.', 'command' => null];
        }

        return [
            'reply' => str($decoded['reply'])->trim()->limit(1000)->toString(),
            'command' => is_array($decoded['command'] ?? null) ? $decoded['command'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $candidate
     */
    private function createAutonomousTask(?array $candidate): ?MacAgentCommand
    {
        if ($candidate === null) {
            return null;
        }

        $device = MacDevice::query()->orderBy('name')->first();

        if ($device === null || ! is_string($candidate['action'] ?? null) || ! is_array($candidate['payload'] ?? null)) {
            return null;
        }

        $action = $candidate['action'];
        $payload = $candidate['payload'];

        if (! $this->isSafePayload($action, $payload)) {
            return null;
        }

        return MacAgentCommand::query()->create([
            'action' => $action,
            'label' => str((string) ($candidate['label'] ?? 'Mac task'))->squish()->limit(120)->toString(),
            'mac_device_id' => $device->id,
            'payload' => $payload,
            'status' => 'approved',
            'requires_approval' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isSafePayload(string $action, array $payload): bool
    {
        if ($action === 'open_url' && is_string($payload['url'] ?? null)) {
            $url = parse_url($payload['url']);

            return is_array($url)
                && ($url['scheme'] ?? null) === 'https'
                && ! isset($url['user'])
                && ! isset($url['pass']);
        }

        if ($action === 'open_path' && is_string($payload['path'] ?? null)) {
            return str_starts_with($payload['path'], '/');
        }

        return $action === 'open_application'
            && is_string($payload['application'] ?? null)
            && preg_match('/^[\pL\pN .\'-]{1,100}$/u', $payload['application']) === 1;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are IRFAN PA, a concise private assistant. Reply in the user's language. You may only start one of these safe Mac actions: open_url with an HTTPS URL without credentials, open_path with an absolute path, or open_application with a simple application name. For a valid safe Mac action, it starts automatically. Never claim the work has completed; say it is starting or being sent to the Mac.

Return only JSON with this exact shape:
{"reply":"short helpful response","command":{"label":"short label","action":"open_url|open_path|open_application","payload":{"url":"https://..."}}}

Use command null if the request is not a clear safe Mac action, asks for anything risky, or needs clarification. Do not include markdown fences.
PROMPT;
    }
}
