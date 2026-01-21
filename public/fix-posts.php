<?php
/**
 * Fix Posts Script - Check and seed posts
 * Access: https://api.smatatech.com.ng/fix-posts.php?token=setup123
 * DELETE THIS FILE AFTER USE!
 */

$setupToken = 'setup123';
if (!isset($_GET['token']) || $_GET['token'] !== $setupToken) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Invalid token. Use ?token=setup123']));
}

header('Content-Type: application/json');

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

$results = [];

try {
    // 1. Check posts table
    $totalPosts = DB::table('posts')->count();
    $publishedPosts = DB::table('posts')->where('status', 'published')->count();
    $postsWithPublishedAt = DB::table('posts')->whereNotNull('published_at')->count();
    $validPublishedPosts = DB::table('posts')
        ->where('status', 'published')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->count();

    $results['current_state'] = [
        'total_posts' => $totalPosts,
        'status_published' => $publishedPosts,
        'has_published_at' => $postsWithPublishedAt,
        'valid_published_posts' => $validPublishedPosts,
    ];

    // 2. Show existing posts details
    $existingPosts = DB::table('posts')->select('id', 'title', 'slug', 'status', 'published_at', 'category_id')->get();
    $results['existing_posts'] = $existingPosts;

    // 3. Fix existing posts - set published_at if null
    $fixedPosts = DB::table('posts')
        ->where('status', 'published')
        ->whereNull('published_at')
        ->update(['published_at' => now()]);
    $results['fixed_published_at'] = $fixedPosts;

    // 4. Check categories
    $categories = DB::table('categories')->select('id', 'name', 'slug', 'status')->get();
    $results['categories'] = $categories;

    // 5. If no posts exist, create sample posts
    if ($totalPosts == 0) {
        $results['action'] = 'Creating sample posts...';
        
        // Get first category
        $category = DB::table('categories')->where('status', 'active')->first();
        
        // Get first admin
        $admin = DB::table('admins')->first();
        
        if ($category) {
            $samplePosts = [
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'title' => 'Building Scalable Laravel Applications',
                    'slug' => 'building-scalable-laravel-applications',
                    'excerpt' => 'Learn the best practices for building Laravel applications that can handle millions of users.',
                    'content' => '<p>Laravel is a powerful PHP framework that makes building web applications a breeze. In this article, we will explore the best practices for building scalable Laravel applications.</p><h2>1. Use Queues for Heavy Processing</h2><p>Move time-consuming tasks to background queues to keep your application responsive.</p><h2>2. Implement Caching Strategically</h2><p>Use Redis or Memcached to cache frequently accessed data.</p><h2>3. Optimize Database Queries</h2><p>Use eager loading to prevent N+1 queries, add proper indexes.</p>',
                    'category_id' => $category->id,
                    'author_id' => $admin ? $admin->id : null,
                    'status' => 'published',
                    'published_at' => now()->subDays(5),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'title' => 'Introduction to React Native',
                    'slug' => 'introduction-to-react-native',
                    'excerpt' => 'Get started with React Native and build cross-platform mobile apps with JavaScript.',
                    'content' => '<p>React Native allows you to build mobile applications using JavaScript and React. Here is how to get started.</p><h2>Why React Native?</h2><ul><li>Write once, run on iOS and Android</li><li>Hot reloading for faster development</li><li>Large ecosystem of libraries</li><li>Native performance</li></ul><h2>Getting Started</h2><p>Install Node.js, the React Native CLI, and start building your first app.</p>',
                    'category_id' => $category->id,
                    'author_id' => $admin ? $admin->id : null,
                    'status' => 'published',
                    'published_at' => now()->subDays(10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'title' => 'Design Principles for Better UX',
                    'slug' => 'design-principles-for-better-ux',
                    'excerpt' => 'Essential design principles that will improve your user experience dramatically.',
                    'content' => '<p>Good design is invisible. Here are the fundamental principles that guide exceptional user experiences.</p><h2>1. Clarity</h2><p>Users should immediately understand what your interface does and how to use it.</p><h2>2. Consistency</h2><p>Use consistent patterns, colors, and interactions throughout your application.</p><h2>3. Feedback</h2><p>Always provide feedback for user actions.</p>',
                    'category_id' => $category->id,
                    'author_id' => $admin ? $admin->id : null,
                    'status' => 'published',
                    'published_at' => now()->subDays(3),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'title' => 'Getting Started with Docker',
                    'slug' => 'getting-started-with-docker',
                    'excerpt' => 'A beginner\'s guide to containerization with Docker.',
                    'content' => '<p>Docker revolutionized how we deploy and manage applications. Let us explore the basics.</p><h2>What is Docker?</h2><p>Docker is a platform for developing, shipping, and running applications in containers.</p><h2>Key Concepts</h2><ul><li>Images: Read-only templates for creating containers</li><li>Containers: Running instances of images</li><li>Dockerfile: Script to build images</li></ul>',
                    'category_id' => $category->id,
                    'author_id' => $admin ? $admin->id : null,
                    'status' => 'published',
                    'published_at' => now()->subDays(7),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'title' => 'AI in Modern Web Applications',
                    'slug' => 'ai-in-modern-web-applications',
                    'excerpt' => 'How to integrate AI capabilities into your web applications.',
                    'content' => '<p>Artificial Intelligence is transforming how we build web applications.</p><h2>Common AI Use Cases</h2><ul><li>Chatbots and virtual assistants</li><li>Content recommendation systems</li><li>Image recognition and processing</li><li>Natural language processing</li></ul><h2>Popular AI APIs</h2><p>Services like OpenAI, Google Cloud AI make it easy to integrate AI.</p>',
                    'category_id' => $category->id,
                    'author_id' => $admin ? $admin->id : null,
                    'status' => 'published',
                    'published_at' => now()->subDays(1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($samplePosts as $post) {
                DB::table('posts')->insert($post);
            }
            
            $results['posts_created'] = count($samplePosts);
        } else {
            $results['error'] = 'No active category found. Create a category first.';
        }
    }

    // 6. Clear cache
    Cache::flush();
    $results['cache'] = 'Cleared';

    // 7. Final count
    $finalCount = DB::table('posts')
        ->where('status', 'published')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->count();
    $results['final_published_count'] = $finalCount;

    $results['test_url'] = url('/api/posts');
    $results['next_step'] = 'DELETE this file after verifying posts work!';

    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'results' => $results
    ], JSON_PRETTY_PRINT);
}
