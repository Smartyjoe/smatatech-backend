# Smatatech API - Backend API Reference

## Overview

This document provides complete API documentation for the Smatatech Laravel Backend API.

- **Base URL**: `https://api.yourdomain.com/api`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content-Type**: `application/json`

---

## Table of Contents

1. [Authentication](#authentication)
   - [Admin Authentication](#admin-authentication)
   - [User Authentication](#user-authentication)
2. [Public Endpoints](#public-endpoints)
3. [Admin Endpoints](#admin-endpoints)
4. [AI Tools Endpoints](#ai-tools-endpoints)
5. [Response Format](#response-format)
6. [Error Codes](#error-codes)

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

---

## Authentication

### Admin Authentication

#### POST /api/admin/login
Login as admin user.

**Auth Required**: No

**Request Body**:
```json
{
  "email": "admin@example.com",
  "password": "your_password"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "Admin Name",
      "email": "admin@example.com",
      "role": "super_admin"
    },
    "token": "1|abc123...",
    "expiresAt": "2024-01-21T12:00:00+00:00"
  }
}
```

**Error Response** (422):
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

#### POST /api/admin/logout
Logout admin user.

**Auth Required**: Yes (Admin)

**Headers**:
```
Authorization: Bearer {token}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Successfully logged out."
}
```

---

#### GET /api/admin/me
Get current admin user info.

**Auth Required**: Yes (Admin)

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Admin Name",
    "email": "admin@example.com",
    "role": "super_admin",
    "lastLoginAt": "2024-01-20T10:00:00+00:00"
  }
}
```

---

#### POST /api/admin/refresh
Refresh admin token.

**Auth Required**: Yes (Admin)

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "2|newtoken...",
    "expiresAt": "2024-01-22T12:00:00+00:00"
  }
}
```

---

### User Authentication

#### POST /api/auth/register
Register a new user.

**Auth Required**: No

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Success Response** (201):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "credits": 50
    },
    "token": "1|abc123...",
    "expiresAt": "2024-01-27T12:00:00+00:00"
  }
}
```

---

#### POST /api/auth/login
Login as user.

**Auth Required**: No

**Request Body**:
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "credits": 50
    },
    "token": "1|abc123...",
    "expiresAt": "2024-01-27T12:00:00+00:00"
  }
}
```

---

#### POST /api/auth/logout
Logout user.

**Auth Required**: Yes (User)

**Success Response** (200):
```json
{
  "success": true,
  "message": "Successfully logged out."
}
```

---

#### GET /api/auth/me
Get current user info.

**Auth Required**: Yes (User)

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "credits": 50,
    "status": "active"
  }
}
```

---

#### POST /api/auth/refresh
Refresh user token.

**Auth Required**: Yes (User)

---

#### POST /api/auth/forgot-password
Request password reset email.

**Auth Required**: No

**Request Body**:
```json
{
  "email": "john@example.com"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Password reset link sent to your email."
}
```

---

#### POST /api/auth/reset-password
Reset password with token.

**Auth Required**: No

**Request Body**:
```json
{
  "token": "reset_token_from_email",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## Public Endpoints

### GET /api/health
Health check endpoint for monitoring.

**Auth Required**: No

**Success Response** (200):
```json
{
  "status": "ok",
  "timestamp": "2024-01-20T12:00:00+00:00",
  "app": "Smatatech API",
  "environment": "production",
  "php_version": "8.2.0",
  "laravel_version": "11.0.0",
  "database": "connected",
  "storage_writable": true
}
```

---

### GET /api/services
Get all published services.

**Auth Required**: No

**Success Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Web Development",
      "slug": "web-development",
      "shortDescription": "Custom web solutions",
      "description": "Full description...",
      "icon": "code",
      "image": "https://...",
      "features": ["Feature 1", "Feature 2"],
      "order": 1
    }
  ]
}
```

---

### GET /api/services/{slug}
Get single service by slug.

**Auth Required**: No

---

### GET /api/case-studies
Get published case studies (paginated).

**Auth Required**: No

**Query Parameters**:
- `per_page` (int, max 50): Items per page

---

### GET /api/case-studies/{slug}
Get single case study by slug.

**Auth Required**: No

---

### GET /api/testimonials
Get published testimonials.

**Auth Required**: No

**Query Parameters**:
- `featured` (boolean): Filter featured only

---

### GET /api/posts
Get published blog posts (paginated).

**Auth Required**: No

**Query Parameters**:
- `per_page` (int, max 50): Items per page
- `category` (string): Filter by category slug
- `search` (string): Search in title and content

**Success Response** (200):
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Blog Post Title",
      "slug": "blog-post-title",
      "excerpt": "Short excerpt...",
      "featuredImage": "https://...",
      "category": {
        "id": "uuid",
        "name": "Category",
        "slug": "category"
      },
      "author": {
        "name": "Author Name"
      },
      "publishedAt": "2024-01-20T10:00:00+00:00"
    }
  ],
  "meta": { ... }
}
```

---

### GET /api/posts/{slug}
Get single blog post by slug.

**Auth Required**: No

---

### GET /api/categories
Get all active blog categories.

**Auth Required**: No

---

### GET /api/brands
Get all active brand logos.

**Auth Required**: No

---

### GET /api/settings
Get public site settings.

**Auth Required**: No

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "siteName": "Smatatech",
    "siteDescription": "...",
    "contactEmail": "contact@example.com",
    "socialLinks": { ... }
  }
}
```

---

### POST /api/contact
Submit contact form.

**Auth Required**: No

**Rate Limit**: 3 requests per minute

**Request Body**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "company": "Company Inc",
  "phone": "+1234567890",
  "projectType": "website",
  "budget": 5000,
  "services": ["web-development", "design"],
  "message": "I need help with..."
}
```

**Success Response** (201):
```json
{
  "success": true,
  "message": "Thank you for your message. We will get back to you soon."
}
```

---

## Admin Endpoints

All admin endpoints require authentication with admin token.

**Headers Required**:
```
Authorization: Bearer {admin_token}
Accept: application/json
Content-Type: application/json
```

### Dashboard

#### GET /api/admin/dashboard/stats
Get dashboard statistics.

**Auth Required**: Yes (Admin - Viewer+)

---

#### GET /api/admin/dashboard/activity
Get recent activity log.

**Auth Required**: Yes (Admin - Viewer+)

---

### User Management

#### GET /api/admin/users
List all users (paginated).

**Auth Required**: Yes (Admin - Admin+)

---

#### GET /api/admin/users/{id}
Get user details.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/users
Create new user.

**Auth Required**: Yes (Admin - Admin+)

**Request Body**:
```json
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123",
  "role": "user",
  "status": "active"
}
```

---

#### PUT /api/admin/users/{id}
Update user.

**Auth Required**: Yes (Admin - Admin+)

---

#### DELETE /api/admin/users/{id}
Delete user.

**Auth Required**: Yes (Admin - Super Admin)

---

#### POST /api/admin/users/{id}/activate
Activate user account.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/users/{id}/deactivate
Deactivate user account.

**Auth Required**: Yes (Admin - Admin+)

---

### Blog Posts

#### GET /api/admin/posts
List all posts (paginated).

**Auth Required**: Yes (Admin - Editor+)

---

#### GET /api/admin/posts/{id}
Get post details.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/posts
Create new post.

**Auth Required**: Yes (Admin - Editor+)

**Request Body**:
```json
{
  "title": "Post Title",
  "slug": "post-title",
  "content": "Post content...",
  "excerpt": "Short excerpt",
  "categoryId": "uuid",
  "featuredImage": "https://...",
  "status": "draft",
  "metaTitle": "SEO Title",
  "metaDescription": "SEO Description"
}
```

---

#### PUT /api/admin/posts/{id}
Update post.

**Auth Required**: Yes (Admin - Editor+)

---

#### DELETE /api/admin/posts/{id}
Delete post.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/posts/{id}/publish
Publish post.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/posts/{id}/unpublish
Unpublish post.

**Auth Required**: Yes (Admin - Editor+)

---

### Categories

#### GET /api/admin/categories
List all categories.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/categories
Create category.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/categories/{id}
Update category.

**Auth Required**: Yes (Admin - Admin+)

---

#### DELETE /api/admin/categories/{id}
Delete category.

**Auth Required**: Yes (Admin - Admin+)

---

### Services

#### GET /api/admin/services
List all services.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/services
Create service.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/services/{id}
Update service.

**Auth Required**: Yes (Admin - Editor+)

---

#### DELETE /api/admin/services/{id}
Delete service.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/services/reorder
Reorder services.

**Auth Required**: Yes (Admin - Editor+)

**Request Body**:
```json
{
  "order": ["uuid1", "uuid2", "uuid3"]
}
```

---

### Case Studies

#### GET /api/admin/case-studies
List all case studies.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/case-studies
Create case study.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/case-studies/{id}
Update case study.

**Auth Required**: Yes (Admin - Editor+)

---

#### DELETE /api/admin/case-studies/{id}
Delete case study.

**Auth Required**: Yes (Admin - Admin+)

---

### Testimonials

#### GET /api/admin/testimonials
List all testimonials.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/testimonials
Create testimonial.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/testimonials/{id}
Update testimonial.

**Auth Required**: Yes (Admin - Editor+)

---

#### DELETE /api/admin/testimonials/{id}
Delete testimonial.

**Auth Required**: Yes (Admin - Admin+)

---

### Brands

#### GET /api/admin/brands
List all brands.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/brands
Create brand.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/brands/{id}
Update brand.

**Auth Required**: Yes (Admin - Editor+)

---

#### DELETE /api/admin/brands/{id}
Delete brand.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/brands/reorder
Reorder brands.

**Auth Required**: Yes (Admin - Editor+)

---

### Comments

#### GET /api/admin/comments
List all comments.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/comments/{id}/approve
Approve comment.

**Auth Required**: Yes (Admin - Editor+)

---

#### POST /api/admin/comments/{id}/reject
Reject comment.

**Auth Required**: Yes (Admin - Editor+)

---

#### DELETE /api/admin/comments/{id}
Delete comment.

**Auth Required**: Yes (Admin - Admin+)

---

### Contacts

#### GET /api/admin/contacts
List all contact submissions.

**Auth Required**: Yes (Admin - Admin+)

---

#### GET /api/admin/contacts/{id}
Get contact details.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/contacts/{id}/read
Mark contact as read.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/contacts/{id}/unread
Mark contact as unread.

**Auth Required**: Yes (Admin - Admin+)

---

#### DELETE /api/admin/contacts/{id}
Delete contact.

**Auth Required**: Yes (Admin - Admin+)

---

### Chatbot Configuration

#### GET /api/admin/chatbot/config
Get chatbot configuration.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/chatbot/config
Update chatbot configuration.

**Auth Required**: Yes (Admin - Admin+)

---

#### POST /api/admin/chatbot/toggle
Toggle chatbot on/off.

**Auth Required**: Yes (Admin - Admin+)

---

### Email Settings

#### GET /api/admin/email/settings
Get email settings.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/email/settings
Update email settings.

**Auth Required**: Yes (Admin - Admin+)

---

#### GET /api/admin/email/templates
List email templates.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/email/templates/{id}
Update email template.

**Auth Required**: Yes (Admin - Admin+)

---

### Site Settings

#### GET /api/admin/settings
Get all site settings.

**Auth Required**: Yes (Admin - Admin+)

---

#### PUT /api/admin/settings
Update site settings.

**Auth Required**: Yes (Admin - Admin+)

---

### File Upload

#### POST /api/admin/upload
Upload a file.

**Auth Required**: Yes (Admin - Editor+)

**Request**: `multipart/form-data`

**Form Fields**:
- `file` (required): The file to upload
- `folder` (optional): Target folder

**Success Response** (201):
```json
{
  "success": true,
  "data": {
    "url": "https://yourdomain.com/storage/uploads/filename.jpg",
    "path": "uploads/filename.jpg"
  }
}
```

---

#### DELETE /api/admin/upload
Delete a file.

**Auth Required**: Yes (Admin - Editor+)

**Request Body**:
```json
{
  "path": "uploads/filename.jpg"
}
```

---

## AI Tools Endpoints

### GET /api/ai/tools
List available AI tools.

**Auth Required**: Yes (User)

---

### GET /api/ai/credits
Get user's credit balance.

**Auth Required**: Yes (User)

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "available": 50,
    "used": 10,
    "total": 60
  }
}
```

---

### POST /api/ai/credits/purchase
Purchase credits (not yet implemented).

**Auth Required**: Yes (User)

---

### GET /api/ai/usage
Get AI usage history.

**Auth Required**: Yes (User)

---

### POST /api/ai/tools/{id}/execute
Execute an AI tool.

**Auth Required**: Yes (User)

**Request Body**:
```json
{
  "input": "Your input text here",
  "options": {}
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthenticated - Missing or invalid token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 405 | Method Not Allowed |
| 422 | Validation Error - Invalid input data |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Server Error |
| 503 | Service Unavailable |

---

## Rate Limits

| Endpoint Type | Limit |
|--------------|-------|
| General API | 60 requests/minute |
| Auth endpoints | 5 requests/minute |
| Admin endpoints | 120 requests/minute |
| Contact form | 3 requests/minute |
| File uploads | 10 requests/minute |
| AI tools | 30 requests/minute |

---

## Admin Role Hierarchy

| Role | Access Level |
|------|--------------|
| super_admin | Full access to all features |
| admin | All features except user deletion and role assignment |
| editor | Content management (posts, services, etc.) |
| viewer | Read-only dashboard access |

---

*Last Updated: January 2024*
