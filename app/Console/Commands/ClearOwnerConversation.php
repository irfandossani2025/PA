<?php

namespace App\Console\Commands;

use App\Models\AssistantConversationMessage;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:clear-owner-conversation {--force : Confirm deletion of the owner conversation history}')]
#[Description('Clear the PA conversation history for the owner account')]
class ClearOwnerConversation extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('Pass --force to clear the owner conversation history.');

            return self::FAILURE;
        }

        $ownerIds = User::query()->where('is_owner', true)->pluck('id');
        $deletedCount = AssistantConversationMessage::query()->whereIn('user_id', $ownerIds)->delete();

        $this->info("Cleared {$deletedCount} conversation messages.");

        return self::SUCCESS;
    }
}
