<?php

use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{postJson, getJson};
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('authenticated attendee with QR scan can create review', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    // Add attendee with QR code and mark attendance
    $qrCode = 'QR_' . Str::random(10);
    $event->attendees()->attach($user->id, ['qr_code' => $qrCode, 'attended_at' => now()]);
    $response = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/reviews", [
        'rating' => 5,
        'comment' => 'Great event!',
    ]);
    $response->assertStatus(201);
    $response->assertJsonStructure(['id', 'rating', 'comment']);
    expect(Review::where('rating', 5)->where('user_id', $user->id)->exists())->toBeTrue();
});
test('attendee without QR scan cannot create review', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    // Add attendee without QR scan
    $event->attendees()->attach($user->id, ['qr_code' => 'QR_' . Str::random(10), 'attended_at' => null]);
    $response = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/reviews", [
        'rating' => 5,
        'comment' => 'Trying to review...',
    ]);
    $response->assertStatus(403);
    expect(Review::count())->toBe(0);
});
test('non-attendee cannot create review', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $response = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/reviews", [
        'rating' => 5,
        'comment' => 'Trying to review...',
    ]);
    $response->assertStatus(403);
    expect(Review::count())->toBe(0);
});
test('unauthenticated user cannot create review', function () {
    $event = Event::factory()->create();
    $response = $this->postJson("api/events/{$event->id}/reviews", [
        'rating' => 5,
        'comment' => 'Trying to review...',
    ]);
    $response->assertStatus(401);
    expect(Review::count())->toBe(0);
});
test('cannot create duplicate review', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $event->attendees()->attach($user->id, ['qr_code' => 'QR_' . Str::random(10), 'attended_at' => now()]);
    $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/reviews", [
        'rating' => 5,
        'comment' => 'Great event!',
    ]);
    $response = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/reviews", [
        'rating' => 4,
        'comment' => 'Trying again...',
    ]);
    $response->assertStatus(403);
    expect(Review::where('user_id', $user->id)->count())->toBe(1);
});
test('can list event reviews', function () {
    $event = Event::factory()->create();
    $user = User::factory()->create();
    Review::factory()->create(['event_id' => $event->id, 'user_id' => $user->id, 'rating' => 4]);
    $response = $this->getJson("api/events/{$event->id}/reviews");
    $response->assertStatus(200);
    $response->assertJsonCount(1);
    $response->assertJsonFragment(['rating' => 4]);
});