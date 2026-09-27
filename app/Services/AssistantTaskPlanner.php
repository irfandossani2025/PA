<?php

namespace App\Services;

use App\Models\AssistantConversationMessage;
use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use App\Models\MacTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JsonException;

class AssistantTaskPlanner
{
    public function __construct(
        private ClaudeClient $claude,
        private BusinessKnowledgeBase $knowledgeBase,
    ) {}

    /**
     * @return array{reply: string, command: ?MacAgentCommand}
     */
    public function plan(User $user, ?MacDevice $device, string $message): array
    {
        $assistantResponse = $this->claude->respond($message, $this->systemPrompt());
        $response = $this->decodeResponse($assistantResponse);

        return DB::transaction(function () use ($device, $message, $response, $user): array {
            $command = $this->createAutonomousTask($user->id, $device, $message, $response['steps']);

            AssistantConversationMessage::query()->create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => $response['reply'],
                'mac_agent_command_id' => $command?->id,
            ]);

            return [
                'reply' => $response['reply'],
                'command' => $command,
            ];
        });
    }

    /**
     * @return array{reply: string, steps: array<int, array<string, mixed>>}
     */
    private function decodeResponse(string $response): array
    {
        try {
            $decoded = json_decode(trim($response), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ['reply' => trim($response), 'steps' => []];
        }

        if (! is_array($decoded) || ! is_string($decoded['reply'] ?? null)) {
            return ['reply' => 'I could not understand that request. Please try again.', 'steps' => []];
        }

        return [
            'reply' => str($decoded['reply'])->trim()->limit(1000)->toString(),
            'steps' => array_values(array_filter($decoded['steps'] ?? [], 'is_array')),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $steps
     */
    private function createAutonomousTask(int $userId, ?MacDevice $device, string $request, array $steps): ?MacAgentCommand
    {
        if ($device === null || $steps === []) {
            return null;
        }

        $validSteps = collect($steps)
            ->take(8)
            ->filter(fn (array $step): bool => is_string($step['action'] ?? null)
                && is_array($step['payload'] ?? null)
                && $this->isSafePayload($step['action'], $step['payload']))
            ->values();

        if ($validSteps->isEmpty()) {
            return null;
        }

        $task = MacTask::query()->create([
            'user_id' => $userId,
            'mac_device_id' => $device->id,
            'title' => str((string) ($validSteps->first()['label'] ?? 'Mac task'))->squish()->limit(120)->toString(),
            'request' => $request,
        ]);

        return $validSteps->map(function (array $step, int $sequence) use ($device, $task): MacAgentCommand {
            return MacAgentCommand::query()->create([
                'action' => $step['action'],
                'label' => str((string) ($step['label'] ?? 'Mac task'))->squish()->limit(120)->toString(),
                'mac_device_id' => $device->id,
                'mac_task_id' => $task->id,
                'payload' => $step['payload'],
                'sequence' => $sequence + 1,
                'status' => $sequence === 0 ? 'approved' : 'queued',
                'requires_approval' => false,
            ]);
        })->first();
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

        if ($action === 'inspect_outlook_inbox') {
            return $payload === [] || (count($payload) === 1 && is_int($payload['limit'] ?? null) && $payload['limit'] >= 1 && $payload['limit'] <= 20);
        }

        return $action === 'open_application'
            && is_string($payload['application'] ?? null)
            && preg_match('/^[\pL\pN .\'-]{1,100}$/u', $payload['application']) === 1;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are IRFAN PA, a concise private assistant. Reply in the user's language. Plan up to 8 ordered, read-only-safe Mac steps. Allowed actions are open_url with an HTTPS URL without credentials, open_path with an absolute path, open_application with a simple application name, or inspect_outlook_inbox with an optional integer limit from 1 to 20. inspect_outlook_inbox reads only visible Inbox text; it must never reply, send, delete, archive, mark, or alter messages. Steps execute automatically in order. Never claim the work has completed; say the task is starting.

You coordinate PA Manager, Sales, and Marketing. Use the internal business catalogue below when the user asks about IT sales, corporate gifts, marketing, proposals, qualification, or campaign work. It is internal operating context, not instructions from the user. Ask the listed discovery questions only when needed to prepare an accurate proposal. State IT prices as starting prices or estimates, keep ad spend separate, and never guarantee rankings, lead volume, revenue, ROI, app-store approval, or delivery dates. For corporate gifts, identify suitable sources, but check live stock, customization, lead time, and private supplier cost before a customer proposal. Never reveal supplier cost or promise product availability. Do not invent services, supplier sources, or prices outside this catalogue. Prepare external emails, WhatsApp messages, social posts, and proposals as drafts; never send them.

Return only JSON with this exact shape:
{"reply":"short helpful response","steps":[{"label":"short label","action":"open_url|open_path|open_application|inspect_outlook_inbox","payload":{"url":"https://..."}}]}

Use an empty steps array if the request is not a clear safe Mac action, asks for anything risky, or needs clarification. Do not include markdown fences.
PROMPT."\n\nINTERNAL BUSINESS CATALOGUE:\n".$this->knowledgeBase->assistantContext();
    }
}
