<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CaseStudiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $caseStudies = [
            [
                'title' => 'E-commerce Growth Platform',
                'slug' => Str::slug('E-commerce Growth Platform'),
                'client' => 'BrightMart',
                'industry' => 'Retail',
                'challenge' => "Stagnant online sales.\n\nLow mobile conversion\nSlow page loads",
                'solution' => "Performance-first rebuild.\n\nMobile-first UX\nOptimized checkout flow",
                'results' => json_encode([
                    ['value' => '+42%', 'label' => 'Conversion rate'],
                    ['value' => '-55%', 'label' => 'Page load time'],
                    ['value' => '+28%', 'label' => 'Average order value'],
                ]),
                'testimonial' => "Smatatech helped us rebuild our commerce stack and the results were immediate.\n- Ada Okeke, Head of Digital",
                'technologies' => ['Laravel', 'React', 'PostgreSQL'],
                'image' => '/images/case-studies/brightmart.jpg',
                'gallery' => [],
                'status' => 'published',
                'meta_title' => 'E-commerce Growth Platform',
                'meta_description' => 'How BrightMart accelerated growth with a new platform.',
            ],
            [
                'title' => 'AI Customer Support Assistant',
                'slug' => Str::slug('AI Customer Support Assistant'),
                'client' => 'FinNova',
                'industry' => 'Fintech',
                'challenge' => "High support volume.\n\nSlow response times\nManual triage overhead",
                'solution' => "AI-powered support automation.\n\nSelf-serve workflows\nAgent assist tooling",
                'results' => json_encode([
                    ['value' => '-60%', 'label' => 'First response time'],
                    ['value' => '+35%', 'label' => 'Customer satisfaction'],
                    ['value' => '24/7', 'label' => 'Support availability'],
                ]),
                'testimonial' => "Our support team now focuses on the most complex issues while AI handles the rest.\n- Tunde Bello, COO",
                'technologies' => ['Python', 'Laravel', 'OpenAI'],
                'image' => '/images/case-studies/finnova.jpg',
                'gallery' => [],
                'status' => 'published',
                'meta_title' => 'AI Customer Support Assistant',
                'meta_description' => 'FinNova reduced support load with AI automation.',
            ],
            [
                'title' => 'Enterprise Analytics Dashboard',
                'slug' => Str::slug('Enterprise Analytics Dashboard'),
                'client' => 'LogiTech Africa',
                'industry' => 'Logistics',
                'challenge' => "Fragmented reporting.\n\nNo unified visibility\nManual exports",
                'solution' => "Unified analytics platform.\n\nReal-time dashboards\nAutomated reporting",
                'results' => json_encode([
                    ['value' => '-70%', 'label' => 'Reporting time'],
                    ['value' => '+25%', 'label' => 'Operational efficiency'],
                    ['value' => '99.9%', 'label' => 'System uptime'],
                ]),
                'testimonial' => "The dashboard gave our leadership the visibility they needed to act faster.\n- Kemi Adeyemi, Director of Ops",
                'technologies' => ['Laravel', 'Vue', 'MySQL'],
                'image' => '/images/case-studies/logitech.jpg',
                'gallery' => [],
                'status' => 'published',
                'meta_title' => 'Enterprise Analytics Dashboard',
                'meta_description' => 'LogiTech Africa unified data into a single analytics hub.',
            ],
        ];

        foreach ($caseStudies as $caseStudy) {
            CaseStudy::updateOrCreate(
                ['title' => $caseStudy['title']],
                $caseStudy
            );
        }
    }
}
