<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create();

        return [
            'name' => $faker->firstName(),
            'email_verified_at' => now(),
            // 'email_verified_at' => null,
            'password' => Hash::make('pass1234'),
            'remember_token' => null,
        ];

        // return [
        //     'name' => $faker->firstName(),
        //     'last_name' => $faker->lastName(),
        //     'address' => $faker->address(),
        //     'phone' => $faker->numerify('09#########'),
        //     'email' => $faker->unique()->safeEmail(),
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('pass1234'),
        //     'profile_photo' => null,
        //     'current_team_id' => null,
        //     'remember_token' => Str::random(10),
        // ];
    }
}



// namespace Database\Factories;

// use Illuminate\Database\Eloquent\Factories\Factory;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;

// /**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
//  */
// class UserFactory extends Factory
// {
//     /**
//      * The current password being used by the factory.
//      */
//     protected static ?string $password;

//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         return [
//             'name' => fake()->name(),
//             'email' => fake()->unique()->safeEmail(),
//             'email_verified_at' => now(),
//             'password' => static::$password ??= Hash::make('password'),
//             'remember_token' => Str::random(10),
//         ];
//     }

//     /**
//      * Indicate that the model's email address should be unverified.
//      */
//     public function unverified(): static
//     {
//         return $this->state(fn (array $attributes) => [
//             'email_verified_at' => null,
//         ]);
//     }
// }
