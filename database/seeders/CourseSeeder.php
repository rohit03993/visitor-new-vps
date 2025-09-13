<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'course_name' => 'None',
                'course_code' => 'NONE',
                'description' => 'General visitor - not interested in any specific course',
                'is_active' => true,
            ],
            [
                'course_name' => 'IIT JEE',
                'course_code' => 'IITJEE',
                'description' => 'IIT Joint Entrance Examination preparation',
                'is_active' => true,
            ],
            [
                'course_name' => 'NEET',
                'course_code' => 'NEET',
                'description' => 'National Eligibility cum Entrance Test preparation',
                'is_active' => true,
            ],
            [
                'course_name' => 'Class 12th',
                'course_code' => '12TH',
                'description' => 'Class 12th board exam preparation',
                'is_active' => true,
            ],
            [
                'course_name' => 'Class 11th',
                'course_code' => '11TH',
                'description' => 'Class 11th board exam preparation',
                'is_active' => true,
            ],
            [
                'course_name' => 'Class 10th',
                'course_code' => '10TH',
                'description' => 'Class 10th board exam preparation',
                'is_active' => true,
            ],
            [
                'course_name' => 'Foundation Course',
                'course_code' => 'FOUNDATION',
                'description' => 'Foundation course for younger students',
                'is_active' => true,
            ],
            [
                'course_name' => 'Olympiad Preparation',
                'course_code' => 'OLYMPIAD',
                'description' => 'Olympiad exam preparation',
                'is_active' => true,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
