<?php

namespace App\Console\Commands;

use App\Services\ClaudeClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pa:claude-health')]
#[Description('Verify the server can reach the configured Claude model')]
class ClaudeHealthCheck extends Command
{
    /**
     * Execute a minimal live request without displaying the response content.
     */
    public function handle(ClaudeClient $claude): int
    {
        $claude->respond('Reply with only: ready.');

        $this->components->info('Claude health check succeeded.');

        return self::SUCCESS;
    }
}
