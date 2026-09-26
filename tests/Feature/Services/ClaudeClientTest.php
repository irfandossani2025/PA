<?php

namespace Tests\Feature\Services;

use App\Services\ClaudeClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ClaudeClientTest extends TestCase
{
    public function test_sends_a_messages_request_with_the_server_side_configuration(): void
    {
        config()->set('services.anthropic.key', 'test-anthropic-key');
        config()->set('services.anthropic.model', 'test-claude-model');
        config()->set('services.anthropic.url', 'https://api.anthropic.com/v1/messages');

        Http::preventStrayRequests();
        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'content' => [['type' => 'text', 'text' => 'I am ready.']],
            ]),
        ]);

        $response = app(ClaudeClient::class)->respond('Prepare my day.', 'You are Irfan\'s assistant.');

        $this->assertSame('I am ready.', $response);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'test-anthropic-key')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && $request['model'] === 'test-claude-model'
                && $request['system'] === "You are Irfan's assistant."
                && $request['messages'][0]['content'] === 'Prepare my day.';
        });
    }

    public function test_requires_server_side_claude_configuration(): void
    {
        config()->set('services.anthropic.key', null);
        config()->set('services.anthropic.model', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Claude is not configured.');

        app(ClaudeClient::class)->respond('Prepare my day.');
    }
}
