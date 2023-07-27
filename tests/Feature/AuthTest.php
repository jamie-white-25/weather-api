<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ]);

        $response->assertStatus(200);
    }

    /**
     * A basic feature test example.
     */
    public function test_user_can_not_login_when_already_logged_in(): void
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ]);

        $response->assertStatus(302);
    }

    /**
     * A basic feature test example.
     */
    public function test_check_validation(): void
    {
        $user = User::factory()->create();

        // Email validation
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'password',
            'device_name' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('email');

        $response = $this->postJson('/api/login', [
            'email' => 'notanemai',
            'password' => 'password',
            'device_name' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('email');

        $response = $this->postJson('/api/login', [
            'email' => 'incorrect@email.com',
            'password' => 'password',
            'device_name' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'User credentials aren\'t valid']);

        // Password validation
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '',
            'device_name' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('password');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'notpassword',
            'device_name' => 'test',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'User credentials aren\'t valid']);

        // Device validation
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('device_name');

        // Device validation
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 123,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('device_name');
    }

    /**
     * A basic feature test example.
     */
    public function test_user_can_logout(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
    }
}
