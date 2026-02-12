<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => Str::slug('Technology'), 'description' => 'Product and engineering insights.'],
            ['name' => 'Business', 'slug' => Str::slug('Business'), 'description' => 'Strategy, growth, and operations.'],
            ['name' => 'AI & Automation', 'slug' => Str::slug('AI & Automation'), 'description' => 'Practical AI use cases.'],
        ];

        $categoryModels = [];
        foreach ($categories as $category) {
            $categoryModels[] = BlogCategory::updateOrCreate(
                ['name' => $category['name']],
                $category + ['post_count' => 0]
            );
        }

        $posts = [
            [
                'title' => 'Building Scalable Web Platforms in 2026',
                'slug' => Str::slug('Building Scalable Web Platforms in 2026'),
                'excerpt' => 'A practical guide to designing scalable systems that grow with your business.',
                'content' => '<p>Scalability starts with clear architecture, reliable infrastructure, and performance budgets.</p>',
                'featured_image' => '/images/blog/scalable-web-platforms.jpg',
                'category_id' => $categoryModels[0]->id ?? null,
                'author' => 'Smatatech Team',
                'tags' => ['Scalability', 'Web', 'Architecture'],
                'status' => 'published',
                'published_at' => now(),
                'meta_title' => 'Building Scalable Web Platforms',
                'meta_description' => 'How to design web platforms that scale.',
            ],
            [
                'title' => 'Using AI to Improve Customer Support',
                'slug' => Str::slug('Using AI to Improve Customer Support'),
                'excerpt' => 'Reduce response times and improve satisfaction with AI-assisted support.',
                'content' => '<p>AI can handle FAQs, triage tickets, and assist agents with recommendations.</p>',
                'featured_image' => '/images/blog/ai-customer-support.jpg',
                'category_id' => $categoryModels[2]->id ?? null,
                'author' => 'Smatatech Team',
                'tags' => ['AI', 'Support', 'Automation'],
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'meta_title' => 'AI for Customer Support',
                'meta_description' => 'Practical AI use cases in support operations.',
            ],
            [
                'title' => 'Digital Transformation Roadmap for SMEs',
                'slug' => Str::slug('Digital Transformation Roadmap for SMEs'),
                'excerpt' => 'Steps small and mid-sized businesses can take to modernize operations.',
                'content' => '<p>Start with core workflows, then invest in data visibility and automation.</p>',
                'featured_image' => '/images/blog/digital-transformation.jpg',
                'category_id' => $categoryModels[1]->id ?? null,
                'author' => 'Smatatech Team',
                'tags' => ['Business', 'Transformation', 'SME'],
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'meta_title' => 'Digital Transformation Roadmap',
                'meta_description' => 'A roadmap for SMEs to modernize.',
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['title' => $post['title']],
                $post
            );
        }

        foreach ($categoryModels as $category) {
            if ($category) {
                $category->update([
                    'post_count' => BlogPost::where('category_id', $category->id)->count(),
                ]);
            }
        }
    }
}
