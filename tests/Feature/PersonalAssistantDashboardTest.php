<?php

namespace Tests\Feature;

use Tests\TestCase;

class PersonalAssistantDashboardTest extends TestCase
{
    public function test_dashboard_renders_the_private_assistant_control_centre(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('IRFAN PA');
        $response->assertSee('Your assistant is taking shape.');
        $response->assertSee('Local foundation only — not yet deployed');
    }
}
