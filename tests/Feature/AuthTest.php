<?php

use App\Models\User;

use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register successfully', function()
{
    $response = postJson('api/register',[
        'name'=>'Test User2',
        'email'=>'testuser2@example.com',
        'password'=>'password123',
        'password_confirmation'=>'password123'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'user' => [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at'
        ],
        'token'
    ]);

    expect($response->json())->toHaveKey('token');

    expect(User::where('email', 'testuser2@example.com')->exists())->toBeTrue();
});

test('fails to register with duplicate email', function () {
    User::factory()->create([
        'name' => 'Test',
        'email' => 'testuser2@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = postJson('api/register', [
        'name' => 'Another User',
        'email' => 'testuser2@example.com',
        'password' => 'password123',
        'password_confirmation'=> 'password123'
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});
