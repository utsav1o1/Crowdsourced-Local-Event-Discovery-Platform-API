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
        $user = auth('sanctum')->user();
        $validated = $request->validate([
            'qr_code' => 'required|string|unique:event_user,qr_code',
        ]);
        $event->attendees()->syncWithoutDetaching([
            $user->id => [
                'qr_code' => $validated['qr_code'],
                'attended_at' => null, 
            ]
        ]);
        return response()->json(['message' => 'Attendee added with QR code.'], 200);
    }
    public function markAttendanceByQR(Request $request, Event $event)
    {
        $validated = $request->validate([
            'qr_code' => 'required|string|exists:event_user,qr_code',
        ]);
        $user = $event->attendees()->wherePivot('qr_code', $validated['qr_code'])->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid QR code.'], 404);
        }
        $event->attendees()->updateExistingPivot($user->id, ['attended_at' => now()]);
        return response()->json(['message' => 'Attendance marked successfully.'], 200);
    }
}
