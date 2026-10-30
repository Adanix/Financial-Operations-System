<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_documentation_is_publicly_accessible(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
    }
}
