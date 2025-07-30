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
test('user can add attendee and mark attendance via QR', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $qrCode = 'QR_' . Str::random(10);
    $response = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/add-attendee", [
        'qr_code' => $qrCode,
    ]);
    $response->assertStatus(200);
    $response->assertJson(['message' => 'Attendee added with QR code.']);
    expect(DB::table('event_user')->where('user_id', $user->id)->where('event_id', $event->id)->where('qr_code', $qrCode)->exists())->toBeTrue();
    $markResponse = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/mark-attendance", [
        'qr_code' => $qrCode,
    ]);
    $markResponse->assertStatus(200);
    $markResponse->assertJson(['message' => 'Attendance marked successfully.']);
    expect(DB::table('event_user')->where('user_id', $user->id)->where('event_id', $event->id)->whereNotNull('attended_at')->exists())->toBeTrue();
});
test('cannot add attendee with duplicate QR code', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $event = Event::factory()->create();
    $qrCode = 'QR_' . Str::random(10);
    $this->actingAs($user1, 'sanctum')->postJson("api/events/{$event->id}/add-attendee", ['qr_code' => $qrCode]);
    $response = $this->actingAs($user2, 'sanctum')->postJson("api/events/{$event->id}/add-attendee", ['qr_code' => $qrCode]);
    $response->assertStatus(422);
});
test('cannot mark attendance with invalid QR code', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $response = $this->actingAs($user, 'sanctum')->postJson("api/events/{$event->id}/mark-attendance", [
        'qr_code' => 'INVALID_QR',
    ]);
    $response->assertStatus(422);
});
test('unauthenticated user cannot add attendee or mark attendance', function () {
    $event = Event::factory()->create();
    $response1 = $this->postJson("api/events/{$event->id}/add-attendee", ['qr_code' => 'QR_' . Str::random(10)]);
    $response2 = $this->postJson("api/events/{$event->id}/mark-attendance", ['qr_code' => 'QR_' . Str::random(10)]);
    $response1->assertStatus(401);
    $response2->assertStatus(401);
});