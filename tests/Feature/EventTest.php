<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('can create an event when authenticated', function () {
    // Create a user and authenticate
    $user = User::factory()->create();

    $eventData = [
        'title' => 'Test Event',
        'description' => 'This is a test event.',
        'latitude' => 27.7172,
        'longitude' => 85.3240,
        'date' => '2025-12-31',
        'image' => 'http://example.com/image.jpg',
        'max_attendees' => 50,
    ];

    // Act as the user and send POST request to create event
    $response = actingAs($user, 'sanctum')->postJson('/api/events', $eventData);

    // Assert the response status and structure
    $response->assertStatus(201)
        ->assertJson(fn ($json) =>
            $json->where('title', $eventData['title'])
                 ->where('approved', false)
                 ->etc()
        );

    // Assert event exists in database with approved = false and user_id matches
    $this->assertDatabaseHas('events', [
        'title' => 'Test Event',
        'user_id' => $user->id,
        'approved' => false,
    ]);
});

it('can show an existing event', function () {
    $user = User::factory()->create();

    $event = Event::factory()->for($user)->create([
        'title' => 'Existing Event',
        'approved' => true,
    ]);

    // Authenticate as any user (could be owner or another)
    actingAs($user, 'sanctum');

    $response = getJson("/api/events/{$event->id}");

    $response->assertStatus(200)
        ->assertJson(fn ($json) =>
            $json->where('id', $event->id)
                 ->where('title', 'Existing Event')
                 ->where('approved', 1)
                 ->etc()
        );
});

it('returns nearby events for authenticated user', function () {
    
    if (DB::connection()->getDriverName() === 'sqlite') {
        $this->markTestSkipped('This test requires MySQL due to usage of geographic functions like ACOS, which are not supported in SQLite.');
        return;
    }
    $user = User::factory()->create();

    // Nearby event
    Event::factory()->create([
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'approved' => true,
    ]);

    // Far away event
    Event::factory()->create([
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'approved' => true,
    ]);

    $response = $this
        ->actingAs($user,'sanctum')
        ->getJson('/api/events/nearby?latitude=40.7128&longitude=-74.0060&radius=50');

    $response->assertOk();
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['latitude' => 40.7128]);
});

it('blocks unauthenticated access to nearby events', function () {
    $response = $this->getJson('/api/events/nearby?latitude=40.7128&longitude=-74.0060&radius=50');

    $response->assertStatus(401);
});

