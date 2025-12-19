<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Create static users
        User::factory()->create([ 'email' => 'prof@gmail.com', 'user_type' => 'prof' ]);
        User::factory()->create([ 'email' => 'student@gmail.com', 'user_type' => 'student' ]);
        User::factory()->create([ 'email' => 'student1@gmail.com', 'user_type' => 'student' ]);
        User::factory()->create([ 'email' => 'parent@gmail.com', 'user_type' => 'parent' ]);

        // Create 15 users, 5 for each user_type
        $userTypes = ['prof', 'student', 'parent'];
        foreach ($userTypes as $userType) {
            User::factory()->count(5)->sequence(
                fn ($sequence) => [
                    'email' => $userType . ($sequence->index + 1) . '@gmail.com',
                    'user_type' => $userType,
                ]
            )->create();
        }
    }
}

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\User;

// class UserSeeder extends Seeder
// {
//     /**
//      * Run the database seeders.
//      */
//     public function run(): void
//     {
//         $userTypes = ['teacher', 'student', 'parent'];

//         foreach ($userTypes as $type) {
//             User::factory()->count(5)->sequence(
//                 fn ($sequence) => [
//                     'email' => $type . $sequence->index + 1 . '@gmail.com',
//                     'user_type' => $type,
//                 ]
//             )->create();
//         }
//     }
// }


// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\User;

// class UserSeeder extends Seeder
// {
//     /**
//      * Run the database seeders.
//      */
//     public function run(): void
//     {
//         // Create 7 users with sequential emails from test1@gmail.com to test7@gmail.com
//         User::factory()->count(7)->sequence(
//             fn ($sequence) => ['email' => 'test' . ($sequence->index + 1) . '@gmail.com']
//         )->create();

//         // // Create 4 regular users
//         // User::factory()->count(4)->create();

//         // // Create 3 staff users explicitly by overriding 'usertype' to 'staff'
//         // User::factory()->count(3)->sequence(
//         //     fn ($sequence) => ['usertype' => 'staff'] // Force 'usertype' to 'staff'
//         // )->create();
//     }
// }



// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\User;

// class UserSeeder extends Seeder
// {
//     /**
//      * Run the database seeders.
//      */
//     public function run(): void
//     {
//         // Create 10 users with alternating 'user' and 'staff' roles
//         User::factory()->count(5)->sequence(
//             fn ($sequence) => [
//                 'email' => 'test' . ($sequence->index + 1) . '@gmail.com',
//             ]
//         )->create();

//         // User::factory()->count(50)->sequence(
//         //     fn ($sequence) => [
//         //         'email' => 'test' . ($sequence->index + 1) . '@gmail.com',
//         //         'usertype' => $sequence->index % 2 === 0 ? 'user' : 'staff'
//         //     ]
//         // )->create();
//     }
// }
