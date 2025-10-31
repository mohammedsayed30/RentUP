<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    protected $abilities = ['orders:read', 'orders:write', 'notify:send', 'devices:write'];

    /** @test */
    public function a_user_can_register_and_receive_a_token()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'email']])
            ->assertJsonFragment(['email' => 'test@example.com']);

        
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function a_user_can_login_and_receive_a_token()
    {
        
        $user = User::factory()->create(['password' => Hash::make('secret')]);

        
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    /** @test */
    public function authenticated_user_can_access_current_user_details()
    {
        
        $user = User::factory()->create();
        $token = $user->createToken('test_token', $this->abilities)->plainTextToken;

       
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }

    /** @test */
    public function authenticated_user_can_logout_by_revoking_token()
    {
       
        $user = User::factory()->create();
        $token = $user->createToken('AuthToken', $this->abilities)->plainTextToken;

       
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User successfully logged out.']);

        
    }
}