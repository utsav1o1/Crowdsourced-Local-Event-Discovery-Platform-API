<?php

namespace Database\Factories;

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'latitude' => fake()->latitude,
            'longitude' => fake()->longitude,
            'date' => fake()->dateTimeBetween('now', '+1 month'),
            'image' => fake()->imageUrl,
            'approved' => false,
            'max_attendees' => fake()->numberBetween(10, 100),
            'user_id' => User::factory(),
        ];
    }
}
