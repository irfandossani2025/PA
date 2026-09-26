<?php

namespace App\Console\Commands;

use App\Models\MacDevice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pa:pair-mac {name : A label for this Mac, such as Irfan MacBook Pro}')]
#[Description('Create a revocable token for one Mac Agent')]
class PairMacAgent extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $token = 'pa_mac_'.str()->random(64);

        $device = MacDevice::query()->create([
            'name' => $this->argument('name'),
            'token_hash' => hash('sha256', $token),
        ]);

        $this->components->info('Mac Agent pairing created.');
        $this->line('Device ID: '.$device->id);
        $this->line('Pairing token (shown once): '.$token);
        $this->newLine();
        $this->warn('Save this token only in the Mac Agent local configuration. Do not paste it into chat, email, or source control.');

        return self::SUCCESS;
    }
}
