<?php

namespace App\Api\Contracts;

use App\Api\ErrorCode;

/**
 * Public API Endpoint Contracts
 */
class PublicContracts
{
    public static function register(): void
    {
        self::registerApiInfo();
        self::registerServices();
        self::registerPosts();
        self::registerCaseStudies();
        self::registerTestimonials();
        self::registerBrands();
        self::registerSettings();
        self::registerContact();
        self::registerChatbot();
        self::registerNewsletter();
        self::registerInquiries();
    }

    protected static function registerApiInfo(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api', 'API Index')
                ->description('Get API information and endpoint overview')
                ->group('info')
                ->tags('info', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('name', 'string', true, 'Smatatech API')
                    ->property('version', 'string', true, '1.0.0')
                    ->property('description', 'string', true)
                    ->property('documentation', 'string', true)
                    ->property('status', 'string', true, 'operational')
                    ->property('endpoints', 'object', true)
                    ->property('authentication', 'object', true)
                )
        );

        ApiRegistry::register(
            EndpointContract::get('/api/docs', 'API Documentation')
                ->description('Get full API documentation')
                ->group('info')
                ->tags('info', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('title', 'string', true)
                    ->property('version', 'string', true)
                    ->property('endpoints', 'object', true)
                    ->property('authentication', 'object', true)
                    ->property('errorCodes', 'object', true)
                )
        );

        ApiRegistry::register(
            EndpointContract::get('/api/meta', 'API Meta Specification')
                ->description('Get complete API specification for programmatic consumption')
                ->group('info')
                ->tags('info', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('apiVersion', 'string', true)
                    ->property('title', 'string', true)
                    ->property('baseUrl', 'string', true)
                    ->property('responseFormat', 'object', true)
                    ->property('errorCodes', 'object', true)
                    ->property('authentication', 'object', true)
                    ->property('endpoints', 'object', true)
                )
        );

        ApiRegistry::register(
            EndpointContract::get('/api/health', 'Health Check')
                ->description('Check API health status')
                ->group('info')
                ->tags('info', 'public', 'monitoring')
                ->public()
                ->response(Schema::object()
                    ->property('status', 'string', true, 'healthy')
                    ->property('database', 'string', true, 'connected')
                    ->property('storage', 'string', true, 'accessible')
                    ->property('timestamp', 'string', true)
                )
        );
    }

    protected static function registerServices(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/services', 'List Services')
                ->description('Get all published services')
                ->group('services')
                ->tags('services', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('title', 'string', true, 'Web Development')
                    ->property('slug', 'string', true, 'web-development')
                    ->property('shortDescription', 'string', true)
                    ->property('fullDescription', 'string', false)
                    ->property('icon', 'string', false)
                    ->property('image', 'string', false)
                    ->property('features', 'array', false)
                    ->property('order', 'integer', true)
                )
                ->errors(ErrorCode::SERVER_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/services/{slug}', 'Get Service')
                ->description('Get service details by slug')
                ->group('services')
                ->tags('services', 'public')
                ->public()
                ->pathParams(Schema::object()
                    ->property('slug', 'string', true, 'web-development')
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('title', 'string', true)
                    ->property('slug', 'string', true)
                    ->property('shortDescription', 'string', true)
                    ->property('fullDescription', 'string', true)
                    ->property('icon', 'string', false)
                    ->property('image', 'string', false)
                    ->property('features', 'array', false)
                    ->property('benefits', 'array', false)
                    ->property('processSteps', 'array', false)
                    ->property('seo', 'object', false)
                )
                ->errors(ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerPosts(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/posts', 'List Posts')
                ->description('Get paginated list of published blog posts')
                ->group('posts')
                ->tags('posts', 'blog', 'public')
                ->public()
                ->queryParams(Schema::object()
                    ->property('page', 'integer', false, 1, 'Page number')
                    ->property('per_page', 'integer', false, 15, 'Items per page (max 50)')
                    ->property('category', 'string', false, null, 'Filter by category slug')
                    ->property('search', 'string', false, null, 'Search in title and content')
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('title', 'string', true)
                    ->property('slug', 'string', true)
                    ->property('excerpt', 'string', false)
                    ->property('featuredImage', 'string', false)
                    ->property('category', 'object', false)
                    ->property('author', 'object', false)
                    ->property('readTime', 'string', false, '5 min read')
                    ->property('isFeatured', 'boolean', true)
                    ->property('publishedAt', 'datetime', true)
                )
                ->errors(ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/posts/{slug}', 'Get Post')
                ->description('Get blog post details by slug')
                ->group('posts')
                ->tags('posts', 'blog', 'public')
                ->public()
                ->pathParams(Schema::object()
                    ->property('slug', 'string', true)
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('title', 'string', true)
                    ->property('slug', 'string', true)
                    ->property('excerpt', 'string', false)
                    ->property('content', 'string', true)
                    ->property('featuredImage', 'string', false)
                    ->property('category', 'object', false)
                    ->property('author', 'object', false)
                    ->property('tags', 'array', false)
                    ->property('readTime', 'string', false)
                    ->property('seo', 'object', false)
                    ->property('commentsEnabled', 'boolean', true)
                    ->property('publishedAt', 'datetime', true)
                )
                ->errors(ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/posts/{slug}/related', 'Get Related Posts')
                ->description('Get posts related by category or tags')
                ->group('posts')
                ->tags('posts', 'blog', 'public')
                ->public()
                ->pathParams(Schema::object()
                    ->property('slug', 'string', true)
                )
                ->response(Schema::array('object'))
                ->errors(ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/categories', 'List Categories')
                ->description('Get all active blog categories')
                ->group('posts')
                ->tags('posts', 'categories', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('name', 'string', true)
                    ->property('slug', 'string', true)
                    ->property('description', 'string', false)
                    ->property('postCount', 'integer', false)
                )
        );
    }

    protected static function registerCaseStudies(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/case-studies', 'List Case Studies')
                ->description('Get paginated list of published case studies')
                ->group('case-studies')
                ->tags('case-studies', 'portfolio', 'public')
                ->public()
                ->queryParams(Schema::object()
                    ->property('page', 'integer', false, 1)
                    ->property('per_page', 'integer', false, 15)
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('title', 'string', true)
                    ->property('slug', 'string', true)
                    ->property('clientName', 'string', true)
                    ->property('industry', 'string', true)
                    ->property('featuredImage', 'string', false)
                    ->property('shortDescription', 'string', false)
                    ->property('highlightStat', 'object', false)
                    ->property('publishDate', 'date', false)
                )
        );

        ApiRegistry::register(
            EndpointContract::get('/api/case-studies/{slug}', 'Get Case Study')
                ->description('Get case study details by slug')
                ->group('case-studies')
                ->tags('case-studies', 'portfolio', 'public')
                ->public()
                ->pathParams(Schema::object()
                    ->property('slug', 'string', true)
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('title', 'string', true)
                    ->property('slug', 'string', true)
                    ->property('clientName', 'string', true)
                    ->property('industry', 'string', true)
                    ->property('duration', 'string', false)
                    ->property('year', 'string', false)
                    ->property('challenge', 'object', false)
                    ->property('solution', 'object', false)
                    ->property('results', 'array', false)
                    ->property('technologies', 'array', false)
                    ->property('testimonial', 'object', false)
                    ->property('gallery', 'array', false)
                    ->property('seo', 'object', false)
                )
                ->errors(ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/case-studies/{slug}/related', 'Get Related Case Studies')
                ->description('Get case studies in same industry')
                ->group('case-studies')
                ->tags('case-studies', 'public')
                ->public()
                ->pathParams(Schema::object()
                    ->property('slug', 'string', true)
                )
                ->response(Schema::array('object'))
                ->errors(ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerTestimonials(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/testimonials', 'List Testimonials')
                ->description('Get all published testimonials')
                ->group('testimonials')
                ->tags('testimonials', 'public')
                ->public()
                ->queryParams(Schema::object()
                    ->property('featured', 'boolean', false, null, 'Filter featured only')
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('clientName', 'string', true)
                    ->property('company', 'string', false)
                    ->property('role', 'string', false)
                    ->property('testimonialText', 'string', true)
                    ->property('avatar', 'string', false)
                    ->property('rating', 'integer', true, 5)
                    ->property('projectType', 'string', false)
                    ->property('isFeatured', 'boolean', true)
                )
        );
    }

    protected static function registerBrands(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/brands', 'List Brands')
                ->description('Get all active brand logos')
                ->group('brands')
                ->tags('brands', 'clients', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('name', 'string', true)
                    ->property('logo', 'string', true, 'Absolute URL to logo image')
                    ->property('website', 'string', false)
                    ->property('websiteUrl', 'string', false)
                    ->property('order', 'integer', true)
                )
        );
    }

    protected static function registerSettings(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/settings', 'Get Site Settings')
                ->description('Get public site settings')
                ->group('settings')
                ->tags('settings', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('siteName', 'string', true)
                    ->property('siteTagline', 'string', false)
                    ->property('siteDescription', 'string', false)
                    ->property('logo', 'object', false)
                    ->property('contact', 'object', false)
                    ->property('socialLinks', 'object', false)
                    ->property('seo', 'object', false)
                    ->property('footer', 'object', false)
                    ->property('heroStats', 'array', false)
                    ->property('features', 'object', false)
                )
        );
    }

    protected static function registerContact(): void
    {
        ApiRegistry::register(
            EndpointContract::post('/api/contact', 'Submit Contact Form')
                ->description('Submit a contact form message')
                ->group('contact')
                ->tags('contact', 'public', 'forms')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('name', 'string', true, 'John Doe', 'Full name')
                    ->property('email', 'email', true, 'john@example.com', 'Email address')
                    ->property('company', 'string', false, 'Acme Inc.', 'Company name')
                    ->property('phone', 'string', false, '+1234567890', 'Phone number')
                    ->property('projectType', 'string', false, 'Web Development', 'Type of project')
                    ->property('budget', 'number', false, 10000, 'Budget amount')
                    ->property('services', 'array', false, null, 'Selected services')
                    ->property('message', 'string', true, null, 'Message content')
                    ->property('metadata', 'object', false, null, 'Tracking metadata')
                    ->required('name', 'email', 'message')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Thank you for your message.')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );
    }

    protected static function registerChatbot(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/chatbot/config', 'Get Chatbot Config')
                ->description('Get public chatbot configuration')
                ->group('chatbot')
                ->tags('chatbot', 'public')
                ->public()
                ->response(Schema::object()
                    ->property('isEnabled', 'boolean', true)
                    ->property('greetingMessage', 'string', true)
                    ->property('suggestedQuestions', 'array', false)
                    ->property('companyInfo', 'object', false)
                )
        );

        ApiRegistry::register(
            EndpointContract::post('/api/chat', 'Send Chat Message')
                ->description('Send a message to the AI chatbot')
                ->group('chatbot')
                ->tags('chatbot', 'public', 'ai')
                ->public()
                ->rateLimit('10 per minute')
                ->requestBody(Schema::object()
                    ->property('message', 'string', true, 'What services do you offer?', 'User message')
                    ->property('conversationId', 'string', false, null, 'Conversation ID for context')
                    ->property('context', 'object', false, null, 'Additional context')
                    ->required('message')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'AI response')
                    ->property('conversationId', 'string', true, 'Conversation ID')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::RATE_LIMIT_EXCEEDED,
                    ErrorCode::FEATURE_DISABLED,
                    ErrorCode::SERVER_ERROR
                )
        );
    }

    protected static function registerNewsletter(): void
    {
        ApiRegistry::register(
            EndpointContract::post('/api/newsletter/subscribe', 'Subscribe to Newsletter')
                ->description('Subscribe an email to the newsletter')
                ->group('newsletter')
                ->tags('newsletter', 'public', 'forms')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('email', 'email', true, 'subscriber@example.com', 'Email address')
                    ->property('consent', 'boolean', true, true, 'GDPR consent')
                    ->required('email', 'consent')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Successfully subscribed!')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );

        ApiRegistry::register(
            EndpointContract::post('/api/newsletter/unsubscribe', 'Unsubscribe from Newsletter')
                ->description('Unsubscribe an email from the newsletter')
                ->group('newsletter')
                ->tags('newsletter', 'public')
                ->public()
                ->requestBody(Schema::object()
                    ->property('email', 'email', true, 'subscriber@example.com')
                    ->required('email')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Successfully unsubscribed.')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::NOT_FOUND
                )
        );
    }

    protected static function registerInquiries(): void
    {
        ApiRegistry::register(
            EndpointContract::post('/api/inquiries', 'Submit Service Inquiry')
                ->description('Submit an inquiry for a specific service')
                ->group('inquiries')
                ->tags('inquiries', 'public', 'forms')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('serviceSlug', 'string', true, 'web-development', 'Service slug')
                    ->property('name', 'string', true, 'John Doe', 'Full name')
                    ->property('email', 'email', true, 'john@example.com', 'Email address')
                    ->property('phone', 'string', false, '+1234567890', 'Phone number')
                    ->property('company', 'string', false, 'Acme Inc.', 'Company name')
                    ->property('budgetRange', 'string', false, '$5,000 - $10,000', 'Budget range')
                    ->property('timeline', 'string', false, '1-3 months', 'Project timeline')
                    ->property('message', 'string', true, null, 'Project details')
                    ->required('serviceSlug', 'name', 'email', 'message')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Inquiry submitted successfully.')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );
    }
}
