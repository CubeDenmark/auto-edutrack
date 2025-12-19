<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'users' => 120, // Fake user count
                'departments' => 5, // Fake department count
                'courses' => 10, // Fake course count
                'sections' => 15, // Fake section count
            ],
            'departmentStats' => collect([
                ['name' => 'Computer Science', 'students' => 40, 'percentage' => 40, 'color' => 'blue'],
                ['name' => 'Information Technology', 'students' => 30, 'percentage' => 30, 'color' => 'green'],
                ['name' => 'Engineering', 'students' => 20, 'percentage' => 20, 'color' => 'red'],
                ['name' => 'Business Administration', 'students' => 10, 'percentage' => 10, 'color' => 'yellow'],
                ['name' => 'Nursing', 'students' => 5, 'percentage' => 5, 'color' => 'purple'],
            ]),
            'recentActivities' => collect([
                ['description' => 'John Doe registered an account', 'created_at' => now()->subMinutes(10)->toDateTimeString()],
                ['description' => 'Jane Smith updated her profile', 'created_at' => now()->subHours(1)->toDateTimeString()],
                ['description' => 'Admin added a new course', 'created_at' => now()->subDays(1)->toDateTimeString()],
                ['description' => 'New student enrolled in Computer Science', 'created_at' => now()->subDays(2)->toDateTimeString()],
                ['description' => 'System maintenance completed', 'created_at' => now()->subDays(3)->toDateTimeString()],
            ]),
        ]);
    }
}
