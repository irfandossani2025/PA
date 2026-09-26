<?php

namespace App\Console\Commands;

use App\Models\MacDevice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pa:mac-agent-status')]
#[Description('Show paired Mac Agents without exposing pairing tokens')]
class MacAgentStatus extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $devices = MacDevice::query()
            ->orderBy('id')
            ->get(['id', 'name', 'last_seen_at']);

        if ($devices->isEmpty()) {
            $this->components->info('No Mac Agents are paired.');

            return self::SUCCESS;
        }

        $this->components->info('Paired Mac Agents:');

        foreach ($devices as $device) {
            $lastSeen = $device->last_seen_at?->toIso8601String() ?? 'Never';

            $this->line("#{$device->id} {$device->name} — last check-in: {$lastSeen}");
        }

        return self::SUCCESS;
    }
}
