<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    /**
     * Seed sample data for all content tables.
     * This ensures API endpoints return meaningful data.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedServices();
        $this->seedPosts();
        $this->seedCaseStudies();
        $this->seedTestimonials();
        $this->seedBrands();

        $this->command->info('Sample data seeded successfully!');
    }

    /**
     * Seed blog categories.
     */
    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Articles about web development, frameworks, and best practices.',
                'status' => 'active',
            ],
            [
                'name' => 'Mobile Apps',
                'slug' => 'mobile-apps',
                'description' => 'iOS, Android, and cross-platform mobile development insights.',
                'status' => 'active',
            ],
            [
                'name' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'description' => 'Design principles, user experience, and interface design tips.',
                'status' => 'active',
            ],
            [
                'name' => 'Cloud & DevOps',
                'slug' => 'cloud-devops',
                'description' => 'Cloud computing, deployment strategies, and DevOps practices.',
                'status' => 'active',
            ],
            [
                'name' => 'AI & Machine Learning',
                'slug' => 'ai-machine-learning',
                'description' => 'Artificial intelligence, machine learning, and data science.',
                'status' => 'active',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        $this->command->info('Categories seeded: ' . count($categories));
    }

    /**
     * Seed services.
     */
    private function seedServices(): void
    {
        $services = [
            [
                'title' => 'Web Development',
                'slug' => 'web-development',
                'short_description' => 'Custom web applications built with modern technologies.',
                'full_description' => 'We build scalable, secure, and high-performance web applications using Laravel, React, Vue.js, and other cutting-edge technologies. Our solutions are tailored to meet your specific business requirements.',
                'icon' => 'code',
                'status' => 'published',
                'order' => 1,
            ],
            [
                'title' => 'Mobile App Development',
                'slug' => 'mobile-app-development',
                'short_description' => 'Native and cross-platform mobile applications.',
                'full_description' => 'From iOS to Android, we develop mobile applications that deliver exceptional user experiences. Our team specializes in React Native, Flutter, and native development for both platforms.',
                'icon' => 'smartphone',
                'status' => 'published',
                'order' => 2,
            ],
            [
                'title' => 'UI/UX Design',
                'slug' => 'ui-ux-design',
                'short_description' => 'User-centered design that converts visitors into customers.',
                'full_description' => 'Our design team creates intuitive, visually stunning interfaces that enhance user engagement. We focus on usability, accessibility, and conversion optimization.',
                'icon' => 'palette',
                'status' => 'published',
                'order' => 3,
            ],
            [
                'title' => 'Cloud Solutions',
                'slug' => 'cloud-solutions',
                'short_description' => 'Scalable cloud infrastructure and deployment.',
                'full_description' => 'We help businesses migrate to the cloud and optimize their infrastructure. Our expertise includes AWS, Google Cloud, Azure, and containerization with Docker and Kubernetes.',
                'icon' => 'cloud',
                'status' => 'published',
                'order' => 4,
            ],
            [
                'title' => 'API Development',
                'slug' => 'api-development',
                'short_description' => 'RESTful and GraphQL APIs for seamless integrations.',
                'full_description' => 'We design and build robust APIs that power your applications and enable third-party integrations. Our APIs are secure, well-documented, and built to scale.',
                'icon' => 'api',
                'status' => 'published',
                'order' => 5,
            ],
            [
                'title' => 'AI Integration',
                'slug' => 'ai-integration',
                'short_description' => 'Integrate AI capabilities into your applications.',
                'full_description' => 'Leverage the power of artificial intelligence in your business. We integrate chatbots, machine learning models, and AI-powered features into existing systems.',
                'icon' => 'brain',
                'status' => 'published',
                'order' => 6,
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['slug' => $service['slug']], $service);
        }

        $this->command->info('Services seeded: ' . count($services));
    }

    /**
     * Seed blog posts.
     */
    private function seedPosts(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Skipping posts seeder.');
            return;
        }

        $posts = [
            [
                'title' => 'Building Scalable Laravel Applications',
                'slug' => 'building-scalable-laravel-applications',
                'excerpt' => 'Learn the best practices for building Laravel applications that can handle millions of users.',
                'content' => '<p>Laravel is a powerful PHP framework that makes building web applications a breeze. In this article, we\'ll explore the best practices for building scalable Laravel applications.</p>
                
<h2>1. Use Queues for Heavy Processing</h2>
<p>Move time-consuming tasks to background queues to keep your application responsive. Laravel\'s queue system supports multiple drivers including Redis, SQS, and database.</p>

<h2>2. Implement Caching Strategically</h2>
<p>Use Redis or Memcached to cache frequently accessed data. Laravel\'s cache system makes it easy to implement caching at various levels.</p>

<h2>3. Optimize Database Queries</h2>
<p>Use eager loading to prevent N+1 queries, add proper indexes, and consider database read replicas for high-traffic applications.</p>

<h2>4. Use Horizontal Scaling</h2>
<p>Design your application to be stateless so you can run multiple instances behind a load balancer.</p>',
                'category_slug' => 'web-development',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Introduction to React Native',
                'slug' => 'introduction-to-react-native',
                'excerpt' => 'Get started with React Native and build cross-platform mobile apps with JavaScript.',
                'content' => '<p>React Native allows you to build mobile applications using JavaScript and React. Here\'s how to get started.</p>

<h2>Why React Native?</h2>
<p>React Native offers several advantages:</p>
<ul>
<li>Write once, run on iOS and Android</li>
<li>Hot reloading for faster development</li>
<li>Large ecosystem of libraries</li>
<li>Native performance</li>
</ul>

<h2>Setting Up Your Environment</h2>
<p>Install Node.js, the React Native CLI, and either Xcode (for iOS) or Android Studio (for Android development).</p>

<h2>Your First App</h2>
<p>Run <code>npx react-native init MyApp</code> to create a new project and start building your first mobile app.</p>',
                'category_slug' => 'mobile-apps',
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Design Principles for Better UX',
                'slug' => 'design-principles-for-better-ux',
                'excerpt' => 'Essential design principles that will improve your user experience dramatically.',
                'content' => '<p>Good design is invisible. Here are the fundamental principles that guide exceptional user experiences.</p>

<h2>1. Clarity</h2>
<p>Users should immediately understand what your interface does and how to use it. Remove ambiguity and be explicit.</p>

<h2>2. Consistency</h2>
<p>Use consistent patterns, colors, and interactions throughout your application. This reduces cognitive load.</p>

<h2>3. Feedback</h2>
<p>Always provide feedback for user actions. Loading states, success messages, and error handling are crucial.</p>

<h2>4. Accessibility</h2>
<p>Design for everyone, including users with disabilities. Use proper contrast, semantic HTML, and ARIA labels.</p>',
                'category_slug' => 'ui-ux-design',
                'status' => 'published',
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Getting Started with Docker',
                'slug' => 'getting-started-with-docker',
                'excerpt' => 'A beginner\'s guide to containerization with Docker.',
                'content' => '<p>Docker revolutionized how we deploy and manage applications. Let\'s explore the basics.</p>

<h2>What is Docker?</h2>
<p>Docker is a platform for developing, shipping, and running applications in containers. Containers are lightweight, portable, and consistent across environments.</p>

<h2>Key Concepts</h2>
<ul>
<li><strong>Images:</strong> Read-only templates for creating containers</li>
<li><strong>Containers:</strong> Running instances of images</li>
<li><strong>Dockerfile:</strong> Script to build images</li>
<li><strong>Docker Compose:</strong> Tool for multi-container applications</li>
</ul>

<h2>Your First Container</h2>
<p>Run <code>docker run hello-world</code> to verify your installation and run your first container.</p>',
                'category_slug' => 'cloud-devops',
                'status' => 'published',
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'AI in Modern Web Applications',
                'slug' => 'ai-in-modern-web-applications',
                'excerpt' => 'How to integrate AI capabilities into your web applications.',
                'content' => '<p>Artificial Intelligence is transforming how we build web applications. Here\'s how you can leverage AI in your projects.</p>

<h2>Common AI Use Cases</h2>
<ul>
<li>Chatbots and virtual assistants</li>
<li>Content recommendation systems</li>
<li>Image recognition and processing</li>
<li>Natural language processing</li>
<li>Predictive analytics</li>
</ul>

<h2>Popular AI APIs</h2>
<p>Services like OpenAI, Google Cloud AI, and AWS AI Services make it easy to integrate AI without building models from scratch.</p>

<h2>Best Practices</h2>
<p>Always consider ethics, privacy, and user consent when implementing AI features in your applications.</p>',
                'category_slug' => 'ai-machine-learning',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($posts as $postData) {
            $category = $categories->firstWhere('slug', $postData['category_slug']);
            
            if (!$category) {
                continue;
            }

            unset($postData['category_slug']);
            $postData['category_id'] = $category->id;

            Post::firstOrCreate(['slug' => $postData['slug']], $postData);
        }

        $this->command->info('Posts seeded: ' . count($posts));
    }

    /**
     * Seed case studies.
     */
    private function seedCaseStudies(): void
    {
        $caseStudies = [
            [
                'title' => 'E-Commerce Platform Redesign',
                'slug' => 'ecommerce-platform-redesign',
                'client' => 'TechRetail Inc.',
                'industry' => 'E-Commerce',
                'summary' => 'Complete redesign of a major e-commerce platform resulting in 150% increase in conversions.',
                'challenge' => 'The client\'s existing platform was outdated, slow, and not mobile-friendly. Cart abandonment was at 78% and mobile users accounted for only 15% of sales.',
                'solution' => 'We redesigned the entire user experience with a mobile-first approach, implemented a new Laravel-based backend with Redis caching, and optimized the checkout flow to reduce friction.',
                'results' => 'Within 6 months: 150% increase in conversions, 60% reduction in cart abandonment, mobile sales increased from 15% to 55% of total revenue.',
                'technologies' => ['Laravel', 'Vue.js', 'Redis', 'AWS', 'Stripe'],
                'status' => 'published',
                'publish_date' => now()->subMonths(2),
            ],
            [
                'title' => 'Healthcare Mobile App',
                'slug' => 'healthcare-mobile-app',
                'client' => 'MediCare Solutions',
                'industry' => 'Healthcare',
                'summary' => 'HIPAA-compliant mobile app for patient-doctor communication and appointment management.',
                'challenge' => 'The healthcare provider needed a secure platform for patient communication, appointment scheduling, and medical record access that complied with strict HIPAA regulations.',
                'solution' => 'Developed a React Native mobile app with end-to-end encryption, secure authentication, and integration with existing EHR systems. Implemented real-time video consultations.',
                'results' => 'Reduced no-show appointments by 40%, patient satisfaction increased by 85%, and the client saved $200K annually in administrative costs.',
                'technologies' => ['React Native', 'Node.js', 'PostgreSQL', 'WebRTC', 'AWS HIPAA'],
                'status' => 'published',
                'publish_date' => now()->subMonths(1),
            ],
            [
                'title' => 'Fintech Dashboard Platform',
                'slug' => 'fintech-dashboard-platform',
                'client' => 'FinanceFlow',
                'industry' => 'Financial Services',
                'summary' => 'Real-time financial analytics dashboard serving 50,000+ daily active users.',
                'challenge' => 'The fintech startup needed a scalable platform to display real-time financial data, portfolio analytics, and market trends to thousands of concurrent users.',
                'solution' => 'Built a microservices architecture with Laravel for the API layer, React for the frontend, and WebSocket connections for real-time data. Implemented horizontal scaling with Kubernetes.',
                'results' => 'Successfully handles 50,000+ DAUs with 99.9% uptime. Average page load time under 1 second. Scaled from 1,000 to 50,000 users in 8 months.',
                'technologies' => ['Laravel', 'React', 'WebSockets', 'Kubernetes', 'PostgreSQL', 'Redis'],
                'status' => 'published',
                'publish_date' => now()->subWeeks(3),
            ],
        ];

        foreach ($caseStudies as $caseStudy) {
            CaseStudy::firstOrCreate(['slug' => $caseStudy['slug']], $caseStudy);
        }

        $this->command->info('Case studies seeded: ' . count($caseStudies));
    }

    /**
     * Seed testimonials.
     */
    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'client_name' => 'Sarah Johnson',
                'client_title' => 'CEO',
                'client_company' => 'TechRetail Inc.',
                'content' => 'Smatatech transformed our e-commerce platform completely. The team\'s expertise in Laravel and Vue.js delivered a solution that exceeded our expectations. Our conversion rate increased by 150% within months.',
                'rating' => 5,
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Michael Chen',
                'client_title' => 'CTO',
                'client_company' => 'FinanceFlow',
                'content' => 'The real-time dashboard they built handles our 50,000+ daily users flawlessly. Their technical skills and attention to detail are outstanding. Highly recommended for any fintech project.',
                'rating' => 5,
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'client_name' => 'Dr. Emily Roberts',
                'client_title' => 'Medical Director',
                'client_company' => 'MediCare Solutions',
                'content' => 'Building a HIPAA-compliant app was our biggest concern, but Smatatech handled it expertly. Patient satisfaction has soared, and we\'ve significantly reduced administrative costs.',
                'rating' => 5,
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'client_name' => 'David Okonkwo',
                'client_title' => 'Founder',
                'client_company' => 'StartupHub Nigeria',
                'content' => 'They built our MVP in record time without compromising on quality. The team understands startup needs and delivers results that help you scale.',
                'rating' => 5,
                'status' => 'published',
                'is_featured' => false,
            ],
            [
                'client_name' => 'Lisa Anderson',
                'client_title' => 'Product Manager',
                'client_company' => 'GlobalTech Solutions',
                'content' => 'Working with Smatatech was a breeze. They communicated clearly, met every deadline, and delivered a product our users love. We\'ve already started our second project with them.',
                'rating' => 5,
                'status' => 'published',
                'is_featured' => false,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(
                [
                    'client_name' => $testimonial['client_name'],
                    'client_company' => $testimonial['client_company'],
                ],
                $testimonial
            );
        }

        $this->command->info('Testimonials seeded: ' . count($testimonials));
    }

    /**
     * Seed brands/partners.
     */
    private function seedBrands(): void
    {
        $brands = [
            [
                'name' => 'TechRetail Inc.',
                'website' => 'https://techretail.example.com',
                'status' => 'active',
                'order' => 1,
            ],
            [
                'name' => 'FinanceFlow',
                'website' => 'https://financeflow.example.com',
                'status' => 'active',
                'order' => 2,
            ],
            [
                'name' => 'MediCare Solutions',
                'website' => 'https://medicare.example.com',
                'status' => 'active',
                'order' => 3,
            ],
            [
                'name' => 'StartupHub Nigeria',
                'website' => 'https://startuphub.ng',
                'status' => 'active',
                'order' => 4,
            ],
            [
                'name' => 'GlobalTech Solutions',
                'website' => 'https://globaltech.example.com',
                'status' => 'active',
                'order' => 5,
            ],
            [
                'name' => 'Innovate Africa',
                'website' => 'https://innovateafrica.example.com',
                'status' => 'active',
                'order' => 6,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand['name']], $brand);
        }

        $this->command->info('Brands seeded: ' . count($brands));
    }
}
