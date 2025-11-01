<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Follow redirects (for example when the app redirects to login)
        $response = $this->followingRedirects()->get('/');

        $response->assertStatus(200);
    }
}
