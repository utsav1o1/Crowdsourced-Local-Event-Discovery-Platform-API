<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $user = auth('sanctum')->user();
    
        // ✅ Check: only users with non-null attended_at (i.e. scanned QR)
        $attended = $event->attendees()
            ->wherePivot('attended_at', '!=', null)
            ->where('user_id', $user->id)
            ->exists();
    
        if (! $attended) {
            return response()->json(['message' => 'You must scan the QR at the event to leave a review.'], 403);
        }
    
        // ✅ Validate
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);
    
        // ✅ Create the review
        $review = $event->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);
    
        return response()->json($review, 201);
    }
}
