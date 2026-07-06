<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'phone_number' => fake()->unique()->numerify('25263#######'),
            'name' => fake()->name(),
            'language' => fake()->randomElement(['so', 'en', 'ar']),
            'delivery_address' => fake()->optional()->address(),
            'fcm_token' => null,
        ];
    }
}
