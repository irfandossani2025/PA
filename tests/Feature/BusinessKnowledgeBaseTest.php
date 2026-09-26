<?php

namespace Tests\Feature;

use App\Services\BusinessKnowledgeBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessKnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_catalogue_is_installed_as_sales_and_marketing_context(): void
    {
        $knowledgeBase = app(BusinessKnowledgeBase::class);

        $this->artisan('app:install-business-knowledge')
            ->expectsOutput('Installed or refreshed 11 IT services and corporate-gift sources for PA Sales and Marketing.')
            ->assertSuccessful();

        $context = $knowledgeBase->assistantContext();

        $this->assertStringContainsString('AI Voice Calling Agents', $context);
        $this->assertStringContainsString('Website & Portal Development', $context);
        $this->assertStringContainsString('Luxury Trading Corporate Gifts', $context);
        $this->assertStringContainsString('Hakplus Corporate Gifts', $context);
        $this->assertStringContainsString('OMR 400.000', $context);
        $this->assertStringContainsString('Do not promise a specific map ranking', $context);
    }
}
