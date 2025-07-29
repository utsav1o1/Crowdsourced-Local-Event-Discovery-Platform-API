<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('user can create event and mark attendee via qr', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $eventData = [
        'title' => 'Tech Meetup',
        'location' => 'Kathmandu',
        'latitude' => 27.7,
        'longitude' => 85.3,
    ];

    $response = $this->postJson('/api/events', $eventData);
    $response->assertCreated();

    $eventId = $response->json('id');

    $attendee = User::factory()->create();
    $qr = 'ABC123XYZ';

    $addAttendee = $this->postJson("/api/events/{$eventId}/add-attendee", [
        'user_id' => $attendee->id,
        'qr_code' => $qr
    ]);
    $addAttendee->assertOk();

    $markAttendance = $this->postJson('/api/events/mark-attendance', [
        'qr_code' => $qr,
        'event_id' => $eventId,
    ]);

    $markAttendance->assertOk()->assertJson(['message' => 'Attendance marked successfully.']);
});