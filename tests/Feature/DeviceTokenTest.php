<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup: Authenticated User with necessary abilities
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test_token', ['devices:write'])->plainTextToken;
    }

    /** @test */
    public function a_user_can_register_a_device_token()
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/devices', [
            'token' => 'fcm_token_12345',
            'platform' => 'android',
        ]);

        $response->assertStatus(201)
           
            ->assertJsonFragment(['message' => 'Device token saved.']); 

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $this->user->id,
            'token' => 'fcm_token_12345',
        ]);
    }

    /** @test */
    public function registering_an_existing_token_updates_it()
    {
        
        DeviceToken::factory()->for($this->user)->create([
            'token' => 'fcm_token_exists',
            'platform' => 'ios',
        ]);

       
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/devices', [
            'token' => 'fcm_token_exists',
            'platform' => 'android',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Device token saved.']); 

        
        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertDatabaseHas('device_tokens', [
            'token' => 'fcm_token_exists',
            'platform' => 'android',
        ]);
    }

    /** @test */
    public function a_user_can_remove_a_device_token()
    {
      
        $device = DeviceToken::factory()->for($this->user)->create();

      
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->deleteJson("/api/v1/devices/{$device->id}");

      
        $response->assertStatus(200); 

        
        $this->assertDatabaseMissing('device_tokens', ['id' => $device->id]);
    }
}