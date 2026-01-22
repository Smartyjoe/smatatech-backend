<?php

namespace App\Api\Contracts;

use App\Api\ErrorCode;

/**
 * Admin API Endpoint Contracts
 */
class AdminContracts
{
    public static function register(): void
    {
        self::registerDashboard();
        self::registerUsers();
        self::registerPosts();
        self::registerCategories();
        self::registerServices();
        self::registerCaseStudies();
        self::registerTestimonials();
        self::registerBrands();
        self::registerContacts();
        self::registerSettings();
        self::registerEmail();
        self::registerChatbot();
        self::registerUpload();
    }

    protected static function registerDashboard(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/dashboard/stats', 'Dashboard Stats')
                ->description('Get dashboard statistics')
                ->group('admin-dashboard')
                ->tags('admin', 'dashboard')
                ->adminAuth()
                ->role('viewer')
                ->response(Schema::object()
                    ->property('totalPosts', 'integer', true)
                    ->property('totalServices', 'integer', true)
                    ->property('totalContacts', 'integer', true)
                    ->property('totalUsers', 'integer', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/admin/dashboard/activity', 'Recent Activity')
                ->description('Get recent activity logs')
                ->group('admin-dashboard')
                ->tags('admin', 'dashboard')
                ->adminAuth()
                ->role('viewer')
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('type', 'string', true)
                    ->property('title', 'string', true)
                    ->property('description', 'string', false)
                    ->property('actorName', 'string', false)
                    ->property('createdAt', 'datetime', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );
    }

    protected static function registerUsers(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/users', 'List Users')
                ->description('Get paginated list of users')
                ->group('admin-users')
                ->tags('admin', 'users')
                ->adminAuth()
                ->role('admin')
                ->queryParams(Schema::object()
                    ->property('page', 'integer', false, 1)
                    ->property('per_page', 'integer', false, 15)
                    ->property('search', 'string', false)
                    ->property('status', 'string', false)
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/admin/users/{id}', 'Get User')
                ->description('Get user details')
                ->group('admin-users')
                ->tags('admin', 'users')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/users', 'Create User')
                ->description('Create a new user')
                ->group('admin-users')
                ->tags('admin', 'users')
                ->adminAuth()
                ->role('admin')
                ->requestBody(Schema::object()
                    ->property('name', 'string', true)
                    ->property('email', 'email', true)
                    ->property('password', 'string', true)
                    ->required('name', 'email', 'password')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/users/{id}', 'Update User')
                ->description('Update user details')
                ->group('admin-users')
                ->tags('admin', 'users')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->requestBody(Schema::object()
                    ->property('name', 'string', false)
                    ->property('email', 'email', false)
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/users/{id}', 'Delete User')
                ->description('Delete a user')
                ->group('admin-users')
                ->tags('admin', 'users')
                ->adminAuth()
                ->role('super_admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerPosts(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/posts', 'List Posts')
                ->description('Get paginated list of all posts')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('editor')
                ->queryParams(Schema::object()
                    ->property('page', 'integer', false, 1)
                    ->property('per_page', 'integer', false, 15)
                    ->property('status', 'string', false)
                    ->property('category_id', 'uuid', false)
                    ->property('search', 'string', false)
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/admin/posts/{id}', 'Get Post')
                ->description('Get post details by ID')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/posts', 'Create Post')
                ->description('Create a new blog post')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('editor')
                ->requestBody(Schema::object()
                    ->property('title', 'string', true)
                    ->property('slug', 'string', false)
                    ->property('excerpt', 'string', false)
                    ->property('content', 'string', true)
                    ->property('categoryId', 'uuid', false)
                    ->property('featuredImage', 'string', false)
                    ->property('tags', 'array', false)
                    ->property('status', 'string', false, 'draft')
                    ->required('title', 'content')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/posts/{id}', 'Update Post')
                ->description('Update a blog post')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/posts/{id}', 'Delete Post')
                ->description('Delete a blog post')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/posts/{id}/publish', 'Publish Post')
                ->description('Publish a blog post')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/posts/{id}/unpublish', 'Unpublish Post')
                ->description('Unpublish a blog post')
                ->group('admin-posts')
                ->tags('admin', 'posts')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerCategories(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/categories', 'List Categories')
                ->description('Get all categories')
                ->group('admin-categories')
                ->tags('admin', 'categories')
                ->adminAuth()
                ->role('editor')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/categories', 'Create Category')
                ->description('Create a new category')
                ->group('admin-categories')
                ->tags('admin', 'categories')
                ->adminAuth()
                ->role('admin')
                ->requestBody(Schema::object()
                    ->property('name', 'string', true)
                    ->property('slug', 'string', false)
                    ->property('description', 'string', false)
                    ->property('status', 'string', false, 'active')
                    ->required('name')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/categories/{id}', 'Update Category')
                ->description('Update a category')
                ->group('admin-categories')
                ->tags('admin', 'categories')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/categories/{id}', 'Delete Category')
                ->description('Delete a category')
                ->group('admin-categories')
                ->tags('admin', 'categories')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerServices(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/services', 'List Services')
                ->description('Get all services')
                ->group('admin-services')
                ->tags('admin', 'services')
                ->adminAuth()
                ->role('editor')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/services', 'Create Service')
                ->description('Create a new service')
                ->group('admin-services')
                ->tags('admin', 'services')
                ->adminAuth()
                ->role('admin')
                ->requestBody(Schema::object()
                    ->property('title', 'string', true)
                    ->property('slug', 'string', false)
                    ->property('shortDescription', 'string', true)
                    ->property('fullDescription', 'string', false)
                    ->property('icon', 'string', false)
                    ->property('features', 'array', false)
                    ->property('status', 'string', false, 'published')
                    ->required('title', 'shortDescription')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/services/{id}', 'Update Service')
                ->description('Update a service')
                ->group('admin-services')
                ->tags('admin', 'services')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/services/{id}', 'Delete Service')
                ->description('Delete a service')
                ->group('admin-services')
                ->tags('admin', 'services')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/services/reorder', 'Reorder Services')
                ->description('Update service display order')
                ->group('admin-services')
                ->tags('admin', 'services')
                ->adminAuth()
                ->role('editor')
                ->requestBody(Schema::object()
                    ->property('items', 'array', true)
                    ->required('items')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );
    }

    protected static function registerCaseStudies(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/case-studies', 'List Case Studies')
                ->description('Get all case studies')
                ->group('admin-case-studies')
                ->tags('admin', 'case-studies')
                ->adminAuth()
                ->role('editor')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/case-studies', 'Create Case Study')
                ->description('Create a new case study')
                ->group('admin-case-studies')
                ->tags('admin', 'case-studies')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/case-studies/{id}', 'Update Case Study')
                ->description('Update a case study')
                ->group('admin-case-studies')
                ->tags('admin', 'case-studies')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/case-studies/{id}', 'Delete Case Study')
                ->description('Delete a case study')
                ->group('admin-case-studies')
                ->tags('admin', 'case-studies')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerTestimonials(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/testimonials', 'List Testimonials')
                ->description('Get all testimonials')
                ->group('admin-testimonials')
                ->tags('admin', 'testimonials')
                ->adminAuth()
                ->role('editor')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/testimonials', 'Create Testimonial')
                ->description('Create a new testimonial')
                ->group('admin-testimonials')
                ->tags('admin', 'testimonials')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/testimonials/{id}', 'Update Testimonial')
                ->description('Update a testimonial')
                ->group('admin-testimonials')
                ->tags('admin', 'testimonials')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/testimonials/{id}', 'Delete Testimonial')
                ->description('Delete a testimonial')
                ->group('admin-testimonials')
                ->tags('admin', 'testimonials')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerBrands(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/brands', 'List Brands')
                ->description('Get all brands')
                ->group('admin-brands')
                ->tags('admin', 'brands')
                ->adminAuth()
                ->role('editor')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/brands', 'Create Brand')
                ->description('Create a new brand')
                ->group('admin-brands')
                ->tags('admin', 'brands')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/brands/{id}', 'Update Brand')
                ->description('Update a brand')
                ->group('admin-brands')
                ->tags('admin', 'brands')
                ->adminAuth()
                ->role('editor')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/brands/{id}', 'Delete Brand')
                ->description('Delete a brand')
                ->group('admin-brands')
                ->tags('admin', 'brands')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/brands/reorder', 'Reorder Brands')
                ->description('Update brand display order')
                ->group('admin-brands')
                ->tags('admin', 'brands')
                ->adminAuth()
                ->role('editor')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );
    }

    protected static function registerContacts(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/contacts', 'List Contacts')
                ->description('Get all contact submissions')
                ->group('admin-contacts')
                ->tags('admin', 'contacts')
                ->adminAuth()
                ->role('admin')
                ->queryParams(Schema::object()
                    ->property('status', 'string', false, null, 'Filter by status')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/contacts/{id}/read', 'Mark Contact Read')
                ->description('Mark a contact as read')
                ->group('admin-contacts')
                ->tags('admin', 'contacts')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/contacts/{id}/unread', 'Mark Contact Unread')
                ->description('Mark a contact as unread')
                ->group('admin-contacts')
                ->tags('admin', 'contacts')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/contacts/{id}', 'Delete Contact')
                ->description('Delete a contact submission')
                ->group('admin-contacts')
                ->tags('admin', 'contacts')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerSettings(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/settings', 'Get Settings')
                ->description('Get all site settings')
                ->group('admin-settings')
                ->tags('admin', 'settings')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/settings', 'Update Settings')
                ->description('Update site settings')
                ->group('admin-settings')
                ->tags('admin', 'settings')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );
    }

    protected static function registerEmail(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/email/settings', 'Get Email Settings')
                ->description('Get email configuration')
                ->group('admin-email')
                ->tags('admin', 'email')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/email/settings', 'Update Email Settings')
                ->description('Update email configuration')
                ->group('admin-email')
                ->tags('admin', 'email')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::get('/api/admin/email/templates', 'List Email Templates')
                ->description('Get all email templates')
                ->group('admin-email')
                ->tags('admin', 'email')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/email/templates/{id}', 'Update Email Template')
                ->description('Update an email template')
                ->group('admin-email')
                ->tags('admin', 'email')
                ->adminAuth()
                ->role('admin')
                ->pathParams(Schema::object()->property('id', 'uuid', true))
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }

    protected static function registerChatbot(): void
    {
        ApiRegistry::register(
            EndpointContract::get('/api/admin/chatbot/config', 'Get Chatbot Config')
                ->description('Get chatbot configuration')
                ->group('admin-chatbot')
                ->tags('admin', 'chatbot')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );

        ApiRegistry::register(
            EndpointContract::put('/api/admin/chatbot/config', 'Update Chatbot Config')
                ->description('Update chatbot configuration')
                ->group('admin-chatbot')
                ->tags('admin', 'chatbot')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::VALIDATION_ERROR)
        );

        ApiRegistry::register(
            EndpointContract::post('/api/admin/chatbot/toggle', 'Toggle Chatbot')
                ->description('Enable or disable chatbot')
                ->group('admin-chatbot')
                ->tags('admin', 'chatbot')
                ->adminAuth()
                ->role('admin')
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN)
        );
    }

    protected static function registerUpload(): void
    {
        ApiRegistry::register(
            EndpointContract::post('/api/admin/upload', 'Upload File')
                ->description('Upload a file')
                ->group('admin-upload')
                ->tags('admin', 'upload', 'files')
                ->adminAuth()
                ->role('editor')
                ->headers(['Content-Type' => 'multipart/form-data'])
                ->requestBody(Schema::object()
                    ->property('file', 'file', true, null, 'File to upload')
                    ->property('folder', 'string', false, null, 'Target folder')
                    ->required('file')
                )
                ->response(Schema::object()
                    ->property('url', 'string', true)
                    ->property('path', 'string', true)
                    ->property('name', 'string', true)
                    ->property('size', 'integer', true)
                    ->property('mimeType', 'string', true)
                )
                ->errors(
                    ErrorCode::AUTH_REQUIRED,
                    ErrorCode::FORBIDDEN,
                    ErrorCode::FILE_TOO_LARGE,
                    ErrorCode::FILE_INVALID_TYPE
                )
        );

        ApiRegistry::register(
            EndpointContract::delete('/api/admin/upload', 'Delete File')
                ->description('Delete an uploaded file')
                ->group('admin-upload')
                ->tags('admin', 'upload', 'files')
                ->adminAuth()
                ->role('editor')
                ->requestBody(Schema::object()
                    ->property('path', 'string', true, '/storage/uploads/file.jpg')
                    ->required('path')
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::FORBIDDEN, ErrorCode::NOT_FOUND)
        );
    }
}
