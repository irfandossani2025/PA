<?php

namespace Tests\Feature\Console;

use App\Services\ClaudeClient;
use Tests\TestCase;

class ClaudeHealthCheckTest extends TestCase
{
    public function test_reports_success_after_claude_responds(): void
    {
        $this->mock(ClaudeClient::class)
            ->shouldReceive('respond')
            ->once()
            ->with('Reply with only: ready.')
            ->andReturn('ready');

        $this->artisan('pa:claude-health')
            ->expectsOutputToContain('Claude health check succeeded.')
            ->assertSuccessful();
    }
}
