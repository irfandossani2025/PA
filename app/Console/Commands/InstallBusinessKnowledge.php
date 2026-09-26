<?php

namespace App\Console\Commands;

use App\Services\BusinessKnowledgeBase;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:install-business-knowledge')]
#[Description('Install or refresh PA internal IT sales and marketing knowledge')]
class InstallBusinessKnowledge extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BusinessKnowledgeBase $knowledgeBase): int
    {
        $count = $knowledgeBase->installDefaultItOfferings();

        $this->info("Installed or refreshed {$count} IT services and corporate-gift sources for PA Sales and Marketing.");

        return self::SUCCESS;
    }
}
