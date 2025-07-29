<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user cannot leave review if attended_at is null (not scanned QR)', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    // Create attendance record but no QR scan (attended_at is null)
    $event->attendees()->attach($user->id, ['attended_at' => null]);

    $response = $this->actingAs($user,'sanctum')->postJson("api/events/{$event->id}/reviews", [
        'rating' => 5,
        'comment' => 'Trying to review...',
    ]);

    $response->assertStatus(403);
    expect(\App\Models\Review::count())->toBe(0);
});

