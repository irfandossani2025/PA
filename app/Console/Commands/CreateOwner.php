<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pa:create-owner {email : The owner sign-in email address} {name=Irfan : A single-word owner name}')]
#[Description('Create the single PA owner account and show its temporary password once')]
class CreateOwner extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (User::query()->where('is_owner', true)->exists()) {
            $this->components->error('An owner account already exists. It was not changed.');

            return self::FAILURE;
        }

        $temporaryPassword = str()->password(20, symbols: false);

        $owner = User::query()->create([
            'name' => (string) $this->argument('name'),
            'email' => (string) $this->argument('email'),
            'password' => $temporaryPassword,
        ]);
        $owner->forceFill(['is_owner' => true])->save();

        $this->components->info('PA owner account created.');
        $this->line('Email: '.$owner->email);
        $this->line('Temporary password (shown once): '.$temporaryPassword);
        $this->warn('Save this password securely. It is shown only once.');

        return self::SUCCESS;
    }
}
