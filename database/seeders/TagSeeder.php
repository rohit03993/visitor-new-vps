<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Enquiry',
                'color' => '#007bff',
                'description' => 'General enquiries and information requests',
            ],
            [
                'name' => 'Demo Class',
                'color' => '#28a745',
                'description' => 'Demo classes and trial sessions',
            ],
            [
                'name' => 'Follow-up',
                'color' => '#ffc107',
                'description' => 'Follow-up meetings and calls',
            ],
            [
                'name' => 'Admission',
                'color' => '#dc3545',
                'description' => 'Admission process and enrollment',
            ],
            [
                'name' => 'Support',
                'color' => '#6f42c1',
                'description' => 'Technical support and assistance',
            ],
            [
                'name' => 'Feedback',
                'color' => '#fd7e14',
                'description' => 'Feedback and suggestions',
            ],
            [
                'name' => 'Complaint',
                'color' => '#e83e8c',
                'description' => 'Complaints and issues',
            ],
            [
                'name' => 'Partnership',
                'color' => '#20c997',
                'description' => 'Business partnerships and collaborations',
            ],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
