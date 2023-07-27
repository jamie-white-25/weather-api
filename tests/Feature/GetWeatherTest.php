<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetWeatherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_only_logged_in_users_can_get_weather(): void
    {
        $response = $this->postJson('/api/weather');

        $response->assertStatus(401);
    }

    /**
     * A basic feature test example.
     */
    public function test_validation_location_and_country_missing(): void
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/weather');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['location', 'country']);
    }

    /**
     * A basic feature test example.
     */
    public function test_validation_location_does_not_exists(): void
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/weather', [
            'location' => 'neverland',
            'country' => 'America',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'The Country could not be determined']);
    }

    /**
     * A basic feature test example.
     */
    public function test_validation_country_does_not_exists(): void
    {
        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/weather', [
            'location' => 'manchester',
            'country' => 'neverland',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'The Country could not be determined']);
    }

    /**
     * A basic feature test example.
     */
    public function test_get_weather_successfully(): void
    {

        Http::preventStrayRequests();

        $geo = file_get_contents(base_path('tests/Feature/Data/ManchesterGeoLocation.json'));
        $weather = file_get_contents(base_path('tests/Feature/Data/ManchesterWeather.json'));

        Http::fake([
            'https://geocoding-api.open-meteo.com/*' => Http::response($geo),
            'https://api.open-meteo.com/*' => Http::response($weather),
        ]);

        $user = Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/weather', [
            'location' => 'manchester',
            'country' => 'england',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'temperature',
                'windspeed',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'temperature' => 19.2,
                'windspeed' => 13.7,
            ],
        ]);
    }
}
