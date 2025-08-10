<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('test123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@test.com',
            'password' => 'test123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }
}


