<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\User;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are professors in the users table
        $professor1 = User::where('user_type', 'prof')->first();
        $professor2 = User::where('user_type', 'prof')->skip(1)->first();

        if (!$professor1 || !$professor2) {
            // If no professors exist, create some
            $professor1 = User::factory()->create([
                'student_school_id' => '123456',
                'name' => 'Dr. John Smith',
                'email' => 'prof1@gmail.com',
                'user_type' => 'prof',
                'password' => bcrypt('pass1234'),
            ]);

            $professor2 = User::factory()->create([
                'name' => 'Dr. Jane Doe',
                'email' => 'prof2@gmail.com',
                'user_type' => 'prof',
                'password' => bcrypt('pass1234'),
            ]);
        }

        // Sample schedule
        $schedule = json_encode([
            ['day' => 'Monday', 'time' => '08:00 AM - 10:00 AM'],
            ['day' => 'Wednesday', 'time' => '10:00 AM - 12:00 PM'],
        ]);

        // Insert classes
        ClassModel::create([
            'name' => 'Web Development',
            'icon' => '💻',
            'term' => 'Fall 2024',
            'description' => 'Introduction to Web Development',
            'schedule' => $schedule,
            'room' => 'Room 101',
            'max_students' => 30,
            'professor_id' => $professor1->id,
        ]);

        ClassModel::create([
            'name' => 'Programming 1',
            'icon' => '⌨️',
            'term' => 'Fall 2024',
            'description' => 'Fundamentals of Programming',
            'schedule' => $schedule,
            'room' => 'Room 202',
            'max_students' => 25,
            'professor_id' => $professor2->id,
        ]);
    }
}

// <?php

// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use App\Models\ClassModel;
// use App\Models\User;

// class ClassSeeder extends Seeder
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         // Ensure professors exist or create them
//         $professor1 = User::firstOrCreate(
//             ['email' => 'prof1@gmail.com'],
//             [
//                 'name' => 'Dr. John Smith',
//                 'user_type' => 'prof',
//                 'password' => bcrypt('pass1234'),
//             ]
//         );

//         $professor2 = User::firstOrCreate(
//             ['email' => 'prof2@gmail.com'],
//             [
//                 'name' => 'Dr. Jane Doe',
//                 'user_type' => 'prof',
//                 'password' => bcrypt('pass1234'),
//             ]
//         );

//         // Sample schedule
//         $schedule = json_encode([
//             ['day' => 'Monday', 'time' => '08:00 AM - 10:00 AM'],
//             ['day' => 'Wednesday', 'time' => '10:00 AM - 12:00 PM'],
//         ]);

//         // Insert classes
//         $classes = [
//             [
//                 'name' => 'Web Development',
//                 'icon' => '💻',
//                 'term' => 'Fall 2024',
//                 'description' => 'Introduction to Web Development',
//                 'schedule' => $schedule,
//                 'room' => 'Room 101',
//                 'max_students' => 30,
//                 'professor_id' => $professor1->id,
//             ],
//             [
//                 'name' => 'Programming 1',
//                 'icon' => '⌨️',
//                 'term' => 'Fall 2024',
//                 'description' => 'Fundamentals of Programming',
//                 'schedule' => $schedule,
//                 'room' => 'Room 202',
//                 'max_students' => 25,
//                 'professor_id' => $professor2->id,
//             ],
//         ];

//         foreach ($classes as $class) {
//             ClassModel::firstOrCreate(['name' => $class['name']], $class);
//         }

//         // Output to console
//         echo "✅ ClassSeeder has been executed successfully!\n";
//     }
// }
