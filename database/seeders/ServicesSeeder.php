<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'title' => 'Web Development',
                'slug' => Str::slug('Web Development'),
                'short_description' => 'Modern, scalable websites and web applications.',
                'long_description' => 'We build fast, secure, and scalable web platforms that drive measurable business outcomes.',
                'icon' => 'code',
                'image' => '/images/services/web-development.jpg',
                'features' => ['Responsive design', 'Performance optimization', 'CMS integration'],
                'benefits' => ['Faster load times', 'Higher conversions', 'Easier content updates'],
                'process' => [
                    ['step' => '1', 'title' => 'Discovery', 'description' => 'Define goals, users, and requirements.'],
                    ['step' => '2', 'title' => 'Build', 'description' => 'Design, develop, and test.'],
                    ['step' => '3', 'title' => 'Launch', 'description' => 'Deploy and monitor.'],
                ],
                'order' => 1,
                'status' => 'active',
                'meta_title' => 'Web Development Services',
                'meta_description' => 'Build modern web platforms with Smatatech.',
            ],
            [
                'title' => 'AI Solutions',
                'slug' => Str::slug('AI Solutions'),
                'short_description' => 'Custom AI and automation for your business.',
                'long_description' => 'From intelligent chatbots to process automation, we help you ship AI that delivers value.',
                'icon' => 'bot',
                'image' => '/images/services/ai-solutions.jpg',
                'features' => ['AI strategy', 'Model integration', 'Automation workflows'],
                'benefits' => ['Reduced costs', 'Faster decisions', 'Improved customer support'],
                'process' => [
                    ['step' => '1', 'title' => 'Assessment', 'description' => 'Identify AI opportunities.'],
                    ['step' => '2', 'title' => 'Implementation', 'description' => 'Build and integrate solutions.'],
                    ['step' => '3', 'title' => 'Optimization', 'description' => 'Monitor and improve.'],
                ],
                'order' => 2,
                'status' => 'active',
                'meta_title' => 'AI Solutions',
                'meta_description' => 'AI-powered solutions tailored to your business.',
            ],
            [
                'title' => 'Mobile App Development',
                'slug' => Str::slug('Mobile App Development'),
                'short_description' => 'iOS and Android apps built for scale.',
                'long_description' => 'We design and build mobile apps that deliver a premium user experience.',
                'icon' => 'smartphone',
                'image' => '/images/services/mobile-apps.jpg',
                'features' => ['Cross-platform builds', 'App store deployment', 'Ongoing support'],
                'benefits' => ['Reach more users', 'Improved engagement', 'Consistent branding'],
                'process' => [
                    ['step' => '1', 'title' => 'Planning', 'description' => 'Define product scope and roadmap.'],
                    ['step' => '2', 'title' => 'Development', 'description' => 'Build and QA the app.'],
                    ['step' => '3', 'title' => 'Release', 'description' => 'Launch and iterate.'],
                ],
                'order' => 3,
                'status' => 'active',
                'meta_title' => 'Mobile App Development',
                'meta_description' => 'Mobile apps that customers love to use.',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['title' => $service['title']],
                $service
            );
        }
    }
}
