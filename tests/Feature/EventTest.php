<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
