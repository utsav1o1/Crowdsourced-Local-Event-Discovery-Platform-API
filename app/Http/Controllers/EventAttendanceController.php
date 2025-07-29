<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventAttendanceController extends Controller
{
    public function addAttendee(Request $request, Event $event)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'qr_code' => 'nullable|string',
        ]);

        $event->attendees()->syncWithoutDetaching([
            $validated['user_id'] => [
                'qr_code' => $validated['qr_code'],
                'attended_at' => now()
            ]
        ]);

        return response()->json(['message' => 'Attendee added successfully.']);
    }

    public function markAttendanceByQR(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::find($validated['event_id']);

        $user = $event->attendees()
                      ->wherePivot('qr_code', $validated['qr_code'])
                      ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid QR or user not registered.'], 404);
        }

        $event->attendees()->updateExistingPivot($user->id, ['attended_at' => now()]);

        return response()->json(['message' => 'Attendance marked successfully.']);
    }
}
