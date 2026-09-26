<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeClient
{
    /**
     * Determine whether the hosted Claude credentials are ready for use.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.anthropic.key')) && filled(config('services.anthropic.model'));
    }

    /**
     * Request a text response from Claude using the configured Messages API model.
     */
    public function respond(string $prompt, ?string $systemPrompt = null): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Claude is not configured. Set ANTHROPIC_API_KEY and ANTHROPIC_MODEL on the server.');
        }

        if (blank(trim($prompt))) {
            throw new RuntimeException('A Claude prompt is required.');
        }

        $payload = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 1024,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt,
            ]],
        ];

        if (filled($systemPrompt)) {
            $payload['system'] = $systemPrompt;
        }

        $response = Http::asJson()
            ->withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => config('services.anthropic.version'),
            ])
            ->connectTimeout(3)
            ->timeout(30)
            ->post(config('services.anthropic.url'), $payload)
            ->throw();

        $textBlock = collect($response->json('content', []))
            ->first(fn (array $block): bool => data_get($block, 'type') === 'text');

        if (! is_array($textBlock) || blank(data_get($textBlock, 'text'))) {
            throw new RuntimeException('Claude returned no text content.');
        }

        return data_get($textBlock, 'text');
    }
}
