# API Requirements Document

## Smatatech Technologies - Laravel Backend API Contract

**Version:** 1.0.0  
**Last Updated:** January 2026  
**Frontend:** React + TypeScript (Vite)  
**Backend:** Laravel REST API  

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [API Response Format](#api-response-format)
4. [Endpoints](#endpoints)
   - [Authentication Endpoints](#1-authentication-endpoints)
   - [User Management](#2-user-management-admin)
   - [Blog & Content Management](#3-blog--content-management)
   - [Services Management](#4-services-management)
   - [Case Studies Management](#5-case-studies-management)
   - [Testimonials Management](#6-testimonials-management)
   - [Brands Management](#7-brands-management)
   - [Contact Messages](#8-contact-messages)
   - [Chatbot Configuration](#9-chatbot-configuration)
   - [Email Settings](#10-email-settings)
   - [Site Settings](#11-site-settings)
   - [Dashboard](#12-dashboard)
   - [File Upload](#13-file-upload)
   - [Public Website Data](#14-public-website-data)
   - [AI Tools (Future)](#15-ai-tools-future)
5. [Error Handling](#error-handling)
6. [CORS Configuration](#cors-configuration)
7. [Rate Limiting](#rate-limiting)

---

## Overview

This document defines the API contract between the Smatatech frontend application and the Laravel backend API. The frontend is designed to work with cross-domain deployments (different cPanel hosting).

### Base URLs

The frontend expects the following environment variables:

```env
VITE_ADMIN_API_BASE_URL=https://api.yourdomain.com/api
VITE_PUBLIC_API_BASE_URL=https://api.yourdomain.com/api
```

### Authentication Method

- **Type:** Bearer Token (JWT or Laravel Sanctum)
- **Header:** `Authorization: Bearer {token}`
- **Token Refresh:** Supported via refresh endpoint

---

## Authentication

### Token Flow

1. Client sends credentials to login endpoint
2. Server returns JWT token with expiration
3. Client stores token and includes in subsequent requests
4. On 401 response, client attempts token refresh
5. If refresh fails, client redirects to login

### Roles

| Role | Description | Access Level |
|------|-------------|--------------|
| `super_admin` | Full system access | All endpoints |
| `admin` | Administrative access | Most admin endpoints |
| `editor` | Content management | Blog, services, case studies |
| `viewer` | Read-only admin access | GET endpoints only |
| `user` | Public website user | Public + user endpoints |
| `subscriber` | Subscribed user | Public + subscriber features |
| `premium` | Premium user | Public + premium + AI tools |

---

## API Response Format

### Success Response

```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

### Paginated Response

```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "currentPage": 1,
    "lastPage": 10,
    "perPage": 15,
    "total": 150,
    "from": 1,
    "to": 15
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## Endpoints

### 1. Authentication Endpoints

#### Admin Authentication

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `POST` | `/admin/login` | No | - | Admin login |
| `POST` | `/admin/logout` | Yes | Admin | Admin logout |
| `GET` | `/admin/me` | Yes | Admin | Get current admin user |
| `POST` | `/admin/refresh` | Yes | Admin | Refresh admin token |

**POST /admin/login**

Request:
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "Admin Name",
      "email": "admin@example.com",
      "avatar": "https://...",
      "role": "super_admin",
      "permissions": ["*"],
      "createdAt": "2024-01-01T00:00:00Z",
      "lastLoginAt": "2024-01-15T10:30:00Z"
    },
    "token": "eyJ...",
    "expiresAt": "2024-01-16T10:30:00Z"
  }
}
```

#### Public User Authentication

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `POST` | `/auth/register` | No | - | User registration |
| `POST` | `/auth/login` | No | - | User login |
| `POST` | `/auth/logout` | Yes | User | User logout |
| `GET` | `/auth/me` | Yes | User | Get current user |
| `POST` | `/auth/refresh` | Yes | User | Refresh user token |
| `POST` | `/auth/forgot-password` | No | - | Request password reset |
| `POST` | `/auth/reset-password` | No | - | Reset password with token |

**POST /auth/register**

Request:
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "user@example.com",
      "role": "user",
      "credits": 50,
      "createdAt": "2024-01-15T10:30:00Z"
    },
    "token": "eyJ...",
    "expiresAt": "2024-01-22T10:30:00Z"
  }
}
```

**POST /auth/forgot-password**

Request:
```json
{
  "email": "user@example.com"
}
```

**POST /auth/reset-password**

Request:
```json
{
  "token": "reset-token-from-email",
  "email": "user@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

### 2. User Management (Admin)

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/users` | Yes | Admin | List all users (paginated) |
| `GET` | `/admin/users/{id}` | Yes | Admin | Get user details |
| `POST` | `/admin/users` | Yes | Admin | Create new user |
| `PUT` | `/admin/users/{id}` | Yes | Admin | Update user |
| `DELETE` | `/admin/users/{id}` | Yes | Super Admin | Delete user |
| `POST` | `/admin/users/{id}/activate` | Yes | Admin | Activate user |
| `POST` | `/admin/users/{id}/deactivate` | Yes | Admin | Deactivate/suspend user |
| `POST` | `/admin/users/{id}/role` | Yes | Super Admin | Assign role to user |

**Query Parameters for GET /admin/users:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number |
| `per_page` | integer | Items per page (default: 15) |
| `search` | string | Search by name or email |
| `sort_by` | string | Sort field (name, email, createdAt) |
| `sort_order` | string | asc or desc |
| `status` | string | Filter by status (active, inactive) |
| `role` | string | Filter by role |

---

### 3. Blog & Content Management

#### Blog Posts

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/posts` | Yes | Editor+ | List all posts (paginated) |
| `GET` | `/admin/posts/{id}` | Yes | Editor+ | Get post details |
| `POST` | `/admin/posts` | Yes | Editor+ | Create new post |
| `PUT` | `/admin/posts/{id}` | Yes | Editor+ | Update post |
| `DELETE` | `/admin/posts/{id}` | Yes | Admin+ | Delete post |
| `POST` | `/admin/posts/{id}/publish` | Yes | Editor+ | Publish post |
| `POST` | `/admin/posts/{id}/unpublish` | Yes | Editor+ | Unpublish post |

**POST /admin/posts**

Request:
```json
{
  "title": "Blog Post Title",
  "excerpt": "Short description...",
  "content": "<p>Full HTML content...</p>",
  "featuredImage": "https://... or base64",
  "categoryId": "category-uuid",
  "status": "draft|published",
  "metaTitle": "SEO Title",
  "metaDescription": "SEO Description",
  "publishedAt": "2024-01-15T10:00:00Z"
}
```

#### Blog Categories

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/categories` | Yes | Editor+ | List all categories |
| `GET` | `/admin/categories/{id}` | Yes | Editor+ | Get category details |
| `POST` | `/admin/categories` | Yes | Admin+ | Create category |
| `PUT` | `/admin/categories/{id}` | Yes | Admin+ | Update category |
| `DELETE` | `/admin/categories/{id}` | Yes | Admin+ | Delete category |

#### Blog Comments

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/comments` | Yes | Editor+ | List all comments |
| `GET` | `/admin/comments/{id}` | Yes | Editor+ | Get comment details |
| `POST` | `/admin/comments/{id}/approve` | Yes | Editor+ | Approve comment |
| `POST` | `/admin/comments/{id}/reject` | Yes | Editor+ | Reject comment |
| `DELETE` | `/admin/comments/{id}` | Yes | Admin+ | Delete comment |

---

### 4. Services Management

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/services` | Yes | Editor+ | List all services |
| `GET` | `/admin/services/{id}` | Yes | Editor+ | Get service details |
| `POST` | `/admin/services` | Yes | Admin+ | Create service |
| `PUT` | `/admin/services/{id}` | Yes | Editor+ | Update service |
| `DELETE` | `/admin/services/{id}` | Yes | Admin+ | Delete service |
| `POST` | `/admin/services/reorder` | Yes | Editor+ | Reorder services |

**POST /admin/services**

Request:
```json
{
  "title": "Web Development",
  "shortDescription": "Custom web solutions...",
  "fullDescription": "<p>Full description...</p>",
  "icon": "code",
  "image": "https://... or base64",
  "status": "published|draft",
  "order": 1
}
```

**POST /admin/services/reorder**

Request:
```json
{
  "orderedIds": ["uuid1", "uuid2", "uuid3"]
}
```

---

### 5. Case Studies Management

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/case-studies` | Yes | Editor+ | List all case studies |
| `GET` | `/admin/case-studies/{id}` | Yes | Editor+ | Get case study details |
| `POST` | `/admin/case-studies` | Yes | Admin+ | Create case study |
| `PUT` | `/admin/case-studies/{id}` | Yes | Editor+ | Update case study |
| `DELETE` | `/admin/case-studies/{id}` | Yes | Admin+ | Delete case study |

**POST /admin/case-studies**

Request:
```json
{
  "title": "Project Title",
  "clientName": "Client Inc.",
  "industry": "Technology",
  "featuredImage": "https://...",
  "problem": "Description of the problem...",
  "solution": "How we solved it...",
  "result": "The outcomes achieved...",
  "status": "published|draft",
  "publishDate": "2024-01-15"
}
```

---

### 6. Testimonials Management

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/testimonials` | Yes | Editor+ | List all testimonials |
| `GET` | `/admin/testimonials/{id}` | Yes | Editor+ | Get testimonial details |
| `POST` | `/admin/testimonials` | Yes | Admin+ | Create testimonial |
| `PUT` | `/admin/testimonials/{id}` | Yes | Editor+ | Update testimonial |
| `DELETE` | `/admin/testimonials/{id}` | Yes | Admin+ | Delete testimonial |

**POST /admin/testimonials**

Request:
```json
{
  "clientName": "John Doe",
  "company": "Acme Inc.",
  "role": "CEO",
  "testimonialText": "Great service...",
  "avatar": "https://...",
  "isFeatured": true,
  "status": "published|draft"
}
```

---

### 7. Brands Management

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/brands` | Yes | Editor+ | List all brands |
| `GET` | `/admin/brands/{id}` | Yes | Editor+ | Get brand details |
| `POST` | `/admin/brands` | Yes | Admin+ | Create brand |
| `PUT` | `/admin/brands/{id}` | Yes | Editor+ | Update brand |
| `DELETE` | `/admin/brands/{id}` | Yes | Admin+ | Delete brand |
| `POST` | `/admin/brands/reorder` | Yes | Editor+ | Reorder brands |

---

### 8. Contact Messages

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/contacts` | Yes | Admin+ | List all contact messages |
| `GET` | `/admin/contacts/{id}` | Yes | Admin+ | Get message details |
| `POST` | `/admin/contacts/{id}/read` | Yes | Admin+ | Mark as read |
| `POST` | `/admin/contacts/{id}/unread` | Yes | Admin+ | Mark as unread |
| `DELETE` | `/admin/contacts/{id}` | Yes | Admin+ | Delete message |

**Public Contact Form Submission:**

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `POST` | `/contact` | No | - | Submit contact form |

**POST /contact**

Request:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "company": "Acme Inc.",
  "phone": "+1234567890",
  "projectType": "Web Development",
  "budget": 25000,
  "services": ["web-development", "ai-solutions"],
  "message": "I would like to discuss..."
}
```

---

### 9. Chatbot Configuration

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/chatbot/config` | Yes | Admin+ | Get chatbot config |
| `PUT` | `/admin/chatbot/config` | Yes | Admin+ | Update chatbot config |
| `POST` | `/admin/chatbot/toggle` | Yes | Admin+ | Enable/disable chatbot |

**PUT /admin/chatbot/config**

Request:
```json
{
  "systemPrompt": "You are a helpful assistant...",
  "personalityTone": "professional|friendly|casual|formal|technical",
  "allowedTopics": ["web development", "AI", "pricing"],
  "restrictedTopics": ["competitors", "internal"],
  "greetingMessage": "Hello! How can I help?",
  "fallbackMessage": "I'm not sure about that...",
  "isEnabled": true,
  "versionLabel": "v1.2"
}
```

---

### 10. Email Settings

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/email/settings` | Yes | Admin+ | Get email settings |
| `PUT` | `/admin/email/settings` | Yes | Admin+ | Update email settings |
| `GET` | `/admin/email/templates` | Yes | Admin+ | List email templates |
| `GET` | `/admin/email/templates/{id}` | Yes | Admin+ | Get template details |
| `PUT` | `/admin/email/templates/{id}` | Yes | Admin+ | Update template |
| `GET` | `/admin/email/brevo` | Yes | Super Admin | Get Brevo config |
| `PUT` | `/admin/email/brevo` | Yes | Super Admin | Update Brevo config |
| `POST` | `/admin/email/brevo/test` | Yes | Super Admin | Test Brevo connection |

---

### 11. Site Settings

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/settings` | Yes | Admin+ | Get site settings |
| `PUT` | `/admin/settings` | Yes | Admin+ | Update site settings |

**PUT /admin/settings**

Request:
```json
{
  "siteName": "Smatatech Technologies",
  "siteDescription": "Digital Solutions Company",
  "contactEmail": "hello@smatatech.com",
  "contactPhone": "+1234567890",
  "address": "123 Tech Street...",
  "socialLinks": {
    "facebook": "https://facebook.com/smatatech",
    "twitter": "https://twitter.com/smatatech",
    "linkedin": "https://linkedin.com/company/smatatech",
    "instagram": "https://instagram.com/smatatech"
  }
}
```

---

### 12. Dashboard

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/admin/dashboard/stats` | Yes | Viewer+ | Get dashboard statistics |
| `GET` | `/admin/dashboard/activity` | Yes | Viewer+ | Get recent activity |

**GET /admin/dashboard/stats Response:**

```json
{
  "success": true,
  "data": {
    "totalPosts": 25,
    "totalUsers": 150,
    "totalContacts": 45,
    "totalServices": 6,
    "totalCaseStudies": 12,
    "totalTestimonials": 8,
    "chatbotStatus": "active|inactive",
    "recentActivity": [
      {
        "id": "uuid",
        "type": "post_created|user_registered|contact_received",
        "title": "Activity title",
        "description": "Activity description",
        "timestamp": "2024-01-15T10:30:00Z",
        "actor": {
          "name": "Admin Name",
          "avatar": "https://..."
        }
      }
    ]
  }
}
```

---

### 13. File Upload

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `POST` | `/admin/upload` | Yes | Editor+ | Upload file |
| `DELETE` | `/admin/upload` | Yes | Editor+ | Delete file |

**POST /admin/upload**

Request: `multipart/form-data`
- `file`: The file to upload
- `folder`: Optional folder name (e.g., "blog", "services")

Response:
```json
{
  "success": true,
  "data": {
    "url": "https://storage.example.com/uploads/image.jpg",
    "filename": "image.jpg"
  }
}
```

---

### 14. Public Website Data

These endpoints are for fetching public data (no authentication required):

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/services` | No | Get published services |
| `GET` | `/services/{slug}` | No | Get service by slug |
| `GET` | `/case-studies` | No | Get published case studies |
| `GET` | `/case-studies/{slug}` | No | Get case study by slug |
| `GET` | `/testimonials` | No | Get published testimonials |
| `GET` | `/posts` | No | Get published blog posts |
| `GET` | `/posts/{slug}` | No | Get blog post by slug |
| `GET` | `/categories` | No | Get blog categories |
| `GET` | `/brands` | No | Get active brands |
| `GET` | `/settings` | No | Get public site settings |

---

### 15. AI Tools (Future)

These endpoints are placeholders for future AI tool integration:

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| `GET` | `/ai/tools` | Yes | User+ | List available AI tools |
| `GET` | `/ai/credits` | Yes | User+ | Get user's credit balance |
| `POST` | `/ai/credits/purchase` | Yes | User+ | Purchase credits |
| `GET` | `/ai/usage` | Yes | User+ | Get usage history |
| `POST` | `/ai/tools/{id}/execute` | Yes | User+ | Execute AI tool |

**GET /ai/credits Response:**

```json
{
  "success": true,
  "data": {
    "available": 100,
    "used": 50,
    "total": 150,
    "expiresAt": "2024-12-31T23:59:59Z"
  }
}
```

**POST /ai/tools/{id}/execute**

Request:
```json
{
  "input": "User input for the AI tool",
  "options": {
    "key": "value"
  }
}
```

Response:
```json
{
  "success": true,
  "data": {
    "output": "AI generated output",
    "creditsUsed": 5,
    "remainingCredits": 95
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Description |
|------|-------------|
| `200` | Success |
| `201` | Created |
| `204` | No Content |
| `400` | Bad Request |
| `401` | Unauthorized |
| `403` | Forbidden |
| `404` | Not Found |
| `422` | Validation Error |
| `429` | Too Many Requests |
| `500` | Server Error |

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required.", "The email must be valid."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## CORS Configuration

The Laravel backend must allow cross-origin requests from the frontend domain:

```php
// config/cors.php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://yourdomain.com',
        'http://localhost:8080', // Development
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

---

## Rate Limiting

Recommended rate limits:

| Endpoint Type | Limit |
|---------------|-------|
| Authentication | 5 requests/minute |
| Public API | 60 requests/minute |
| Admin API | 120 requests/minute |
| File Upload | 10 requests/minute |
| AI Tools | Based on credits |

---

## Implementation Notes

1. **UUID Primary Keys**: All resources use UUIDs for IDs
2. **Timestamps**: All dates in ISO 8601 format (UTC)
3. **Soft Deletes**: Implement soft deletes for recoverable data
4. **Audit Logging**: Log all admin actions for the activity feed
5. **Image Processing**: Resize and optimize uploaded images
6. **Search**: Implement full-text search for content endpoints
7. **Caching**: Cache public endpoints for performance

---

## Changelog

### v1.0.0 (January 2026)
- Initial API contract
- Admin authentication
- Public user authentication
- Content management endpoints
- AI tools placeholders
