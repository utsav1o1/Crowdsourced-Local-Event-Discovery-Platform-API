<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $user = auth('sanctum')->user();
      
        if (!$event->attendees()->where('user_id', $user->id)->whereNotNull('event_user.attended_at')->exists()) {
            return response()->json(['message' => 'You must scan the QR code at the event to leave a review.'], 403);
        }
      
        if ($event->reviews()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You have already reviewed this event.'], 403);
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);
       
        $review = $event->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);
        return response()->json($review, 201);
    }
    public function index(Event $event)
    {
        $reviews = $event->reviews()->with('user:id,name')->get();
        return response()->json($reviews, 200);
    }
}
