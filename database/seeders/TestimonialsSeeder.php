<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'client_name' => 'Ada Okeke',
                'client_image' => '/images/testimonials/ada-okeke.jpg',
                'company' => 'BrightMart',
                'role' => 'Head of Digital',
                'text' => 'Smatatech delivered a high-performing platform that improved our conversion rate.',
                'rating' => 5,
                'project_type' => 'web',
                'featured' => true,
                'status' => 'active',
            ],
            [
                'client_name' => 'Tunde Bello',
                'client_image' => '/images/testimonials/tunde-bello.jpg',
                'company' => 'FinNova',
                'role' => 'COO',
                'text' => 'Their AI automation reduced our support backlog significantly.',
                'rating' => 5,
                'project_type' => 'ai',
                'featured' => false,
                'status' => 'active',
            ],
            [
                'client_name' => 'Kemi Adeyemi',
                'client_image' => '/images/testimonials/kemi-adeyemi.jpg',
                'company' => 'LogiTech Africa',
                'role' => 'Director of Ops',
                'text' => 'The analytics dashboard provides real-time visibility across operations.',
                'rating' => 5,
                'project_type' => 'analytics',
                'featured' => false,
                'status' => 'active',
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['client_name' => $testimonial['client_name'], 'company' => $testimonial['company']],
                $testimonial
            );
        }
    }
}
