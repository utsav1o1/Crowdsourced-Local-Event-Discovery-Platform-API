<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         // Create users and events
         $users = User::factory(10)->create();
         $events = Event::factory(20)->create();
 
         // Assign each user to random events and leave a review if attended
         foreach ($users as $user) {
             $randomEvents = $events->random(rand(1, 3));
 
             foreach ($randomEvents as $event) {
                 // Attach user to event as attendee with qr_code and attended_at
                 $event->attendees()->attach($user->id, [
                     'qr_code' => uniqid('QR_'),
                     'attended_at' => now(),
                     'created_at' => now(),
                     'updated_at' => now()
                 ]);
 
                 // Leave a review
                 Review::factory()->create([
                     'event_id' => $event->id,
                     'user_id' => $user->id
                 ]);
             }
         }
    }
}
