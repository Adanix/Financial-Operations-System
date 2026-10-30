<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_publicly_accessible_and_shows_contact(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('nixresponding@gmail.com');
        $response->assertSee('SoloLedger');
    }

    public function test_unauthenticated_user_accessing_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_documentation_is_publicly_accessible(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
    }
}
