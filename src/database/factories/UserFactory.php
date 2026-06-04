<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at'=> now(),
            'password'          => bcrypt('password'), 
            'phone'             => $this->faker->optional()->phoneNumber(),
            'rank'              => $this->faker->randomElement(['Beginner', 'Intermediate', 'Advanced', 'Professional']),
            'elo_rating'        => $this->faker->numberBetween(800, 2200),
            'virtual_coins'     => $this->faker->numberBetween(1000, 15000),
            'wins'              => $this->faker->numberBetween(5, 120),
            'losses'            => $this->faker->numberBetween(0, 80),
            'bio'               => 'Đam mê cầu lông ' . $this->faker->sentence(8),
            'avatar'            => 'https://picsum.photos/id/' . rand(1, 200) . '/300/300',
            'role'              => 'user',
            'remember_token'    => Str::random(10),
        ];
    }
}
