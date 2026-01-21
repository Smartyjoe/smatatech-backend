# Smatatech API - All Endpoints

**Base URL:** `https://api.smatatech.com.ng/api`

---

## Table of Contents

1. [Public Endpoints](#public-endpoints)
2. [User Authentication](#user-authentication)
3. [Admin Authentication](#admin-authentication)
4. [Admin Dashboard](#admin-dashboard)
5. [Admin User Management](#admin-user-management)
6. [Admin Blog Posts](#admin-blog-posts)
7. [Admin Categories](#admin-categories)
8. [Admin Comments](#admin-comments)
9. [Admin Services](#admin-services)
10. [Admin Case Studies](#admin-case-studies)
11. [Admin Testimonials](#admin-testimonials)
12. [Admin Brands](#admin-brands)
13. [Admin Contacts](#admin-contacts)
14. [Admin Chatbot](#admin-chatbot)
15. [Admin Email Settings](#admin-email-settings)
16. [Admin Site Settings](#admin-site-settings)
17. [Admin File Upload](#admin-file-upload)
18. [AI Tools](#ai-tools)

---

## Public Endpoints

No authentication required.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/` | API information and available endpoints |
| `GET` | `/health` | Health check (database, storage status) |
| `GET` | `/settings` | Get public site settings |
| `GET` | `/services` | List all published services |
| `GET` | `/services/{slug}` | Get single service by slug |
| `GET` | `/case-studies` | List all published case studies |
| `GET` | `/case-studies/{slug}` | Get single case study by slug |
| `GET` | `/testimonials` | List all published testimonials |
| `GET` | `/posts` | List all published blog posts |
| `GET` | `/posts/{slug}` | Get single blog post by slug |
| `GET` | `/categories` | List all active categories |
| `GET` | `/brands` | List all active brands |
| `POST` | `/contact` | Submit contact form (rate limited: 3/min) |

---

## User Authentication

Prefix: `/auth`

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/auth/register` | Register new user | No |
| `POST` | `/auth/login` | User login | No |
| `POST` | `/auth/forgot-password` | Request password reset | No |
| `POST` | `/auth/reset-password` | Reset password with token | No |
| `POST` | `/auth/logout` | User logout | Yes (User) |
| `GET` | `/auth/me` | Get current user info | Yes (User) |
| `POST` | `/auth/refresh` | Refresh auth token | Yes (User) |

---

## Admin Authentication

Prefix: `/admin`

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| `POST` | `/admin/login` | Admin login | No |
| `POST` | `/admin/logout` | Admin logout | Yes (Admin) |
| `GET` | `/admin/me` | Get current admin info | Yes (Admin) |
| `POST` | `/admin/refresh` | Refresh admin token | Yes (Admin) |

---

## Admin Dashboard

Prefix: `/admin/dashboard`  
Required Role: `viewer` or higher

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/dashboard/stats` | Get dashboard statistics |
| `GET` | `/admin/dashboard/activity` | Get recent activity log |

---

## Admin User Management

Prefix: `/admin/users`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/users` | List all users | Admin |
| `GET` | `/admin/users/{id}` | Get user details | Admin |
| `POST` | `/admin/users` | Create new user | Admin |
| `PUT` | `/admin/users/{id}` | Update user | Admin |
| `POST` | `/admin/users/{id}/activate` | Activate user | Admin |
| `POST` | `/admin/users/{id}/deactivate` | Deactivate user | Admin |
| `DELETE` | `/admin/users/{id}` | Delete user | Super Admin |
| `POST` | `/admin/users/{id}/role` | Assign role to user | Super Admin |

---

## Admin Blog Posts

Prefix: `/admin/posts`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/posts` | List all posts | Editor |
| `GET` | `/admin/posts/{id}` | Get post details | Editor |
| `POST` | `/admin/posts` | Create new post | Editor |
| `PUT` | `/admin/posts/{id}` | Update post | Editor |
| `POST` | `/admin/posts/{id}/publish` | Publish post | Editor |
| `POST` | `/admin/posts/{id}/unpublish` | Unpublish post | Editor |
| `DELETE` | `/admin/posts/{id}` | Delete post | Admin |

---

## Admin Categories

Prefix: `/admin/categories`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/categories` | List all categories | Editor |
| `GET` | `/admin/categories/{id}` | Get category details | Editor |
| `POST` | `/admin/categories` | Create category | Admin |
| `PUT` | `/admin/categories/{id}` | Update category | Admin |
| `DELETE` | `/admin/categories/{id}` | Delete category | Admin |

---

## Admin Comments

Prefix: `/admin/comments`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/comments` | List all comments | Editor |
| `GET` | `/admin/comments/{id}` | Get comment details | Editor |
| `POST` | `/admin/comments/{id}/approve` | Approve comment | Editor |
| `POST` | `/admin/comments/{id}/reject` | Reject comment | Editor |
| `DELETE` | `/admin/comments/{id}` | Delete comment | Admin |

---

## Admin Services

Prefix: `/admin/services`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/services` | List all services | Editor |
| `GET` | `/admin/services/{id}` | Get service details | Editor |
| `PUT` | `/admin/services/{id}` | Update service | Editor |
| `POST` | `/admin/services/reorder` | Reorder services | Editor |
| `POST` | `/admin/services` | Create service | Admin |
| `DELETE` | `/admin/services/{id}` | Delete service | Admin |

---

## Admin Case Studies

Prefix: `/admin/case-studies`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/case-studies` | List all case studies | Editor |
| `GET` | `/admin/case-studies/{id}` | Get case study details | Editor |
| `PUT` | `/admin/case-studies/{id}` | Update case study | Editor |
| `POST` | `/admin/case-studies` | Create case study | Admin |
| `DELETE` | `/admin/case-studies/{id}` | Delete case study | Admin |

---

## Admin Testimonials

Prefix: `/admin/testimonials`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/testimonials` | List all testimonials | Editor |
| `GET` | `/admin/testimonials/{id}` | Get testimonial details | Editor |
| `PUT` | `/admin/testimonials/{id}` | Update testimonial | Editor |
| `POST` | `/admin/testimonials` | Create testimonial | Admin |
| `DELETE` | `/admin/testimonials/{id}` | Delete testimonial | Admin |

---

## Admin Brands

Prefix: `/admin/brands`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/brands` | List all brands | Editor |
| `GET` | `/admin/brands/{id}` | Get brand details | Editor |
| `PUT` | `/admin/brands/{id}` | Update brand | Editor |
| `POST` | `/admin/brands/reorder` | Reorder brands | Editor |
| `POST` | `/admin/brands` | Create brand | Admin |
| `DELETE` | `/admin/brands/{id}` | Delete brand | Admin |

---

## Admin Contacts

Prefix: `/admin/contacts`  
Required Role: `admin` or higher

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/contacts` | List all contact submissions |
| `GET` | `/admin/contacts/{id}` | Get contact details |
| `POST` | `/admin/contacts/{id}/read` | Mark contact as read |
| `POST` | `/admin/contacts/{id}/unread` | Mark contact as unread |
| `DELETE` | `/admin/contacts/{id}` | Delete contact |

---

## Admin Chatbot

Prefix: `/admin/chatbot`  
Required Role: `admin` or higher

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/chatbot/config` | Get chatbot configuration |
| `PUT` | `/admin/chatbot/config` | Update chatbot configuration |
| `POST` | `/admin/chatbot/toggle` | Toggle chatbot on/off |

---

## Admin Email Settings

Prefix: `/admin/email`

| Method | Endpoint | Description | Required Role |
|--------|----------|-------------|---------------|
| `GET` | `/admin/email/settings` | Get email settings | Admin |
| `PUT` | `/admin/email/settings` | Update email settings | Admin |
| `GET` | `/admin/email/templates` | List email templates | Admin |
| `GET` | `/admin/email/templates/{id}` | Get email template | Admin |
| `PUT` | `/admin/email/templates/{id}` | Update email template | Admin |
| `GET` | `/admin/email/brevo` | Get Brevo configuration | Super Admin |
| `PUT` | `/admin/email/brevo` | Update Brevo configuration | Super Admin |
| `POST` | `/admin/email/brevo/test` | Test Brevo connection | Super Admin |

---

## Admin Site Settings

Prefix: `/admin/settings`  
Required Role: `admin` or higher

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/settings` | Get all site settings |
| `PUT` | `/admin/settings` | Update site settings |

---

## Admin File Upload

Prefix: `/admin/upload`  
Required Role: `editor` or higher

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/admin/upload` | Upload a file |
| `DELETE` | `/admin/upload` | Delete a file |

---

## AI Tools

Prefix: `/ai`  
Required: User Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/ai/tools` | List available AI tools |
| `GET` | `/ai/credits` | Get user's credit balance |
| `POST` | `/ai/credits/purchase` | Purchase credits |
| `GET` | `/ai/usage` | Get AI usage history |
| `POST` | `/ai/tools/{id}/execute` | Execute an AI tool |

---

## Role Hierarchy

| Role | Access Level |
|------|--------------|
| `super_admin` | Full access to all features |
| `admin` | All features except user deletion and Brevo config |
| `editor` | Content management (posts, services, etc.) |
| `viewer` | Read-only dashboard access |

---

## Authentication Headers

For all authenticated endpoints, include:

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## Rate Limits

| Endpoint Type | Limit |
|--------------|-------|
| General API | 60 requests/minute |
| Auth endpoints | 5 requests/minute |
| Contact form | 3 requests/minute |
| File uploads | Rate limited |

---

## Quick Reference - Total Endpoints

| Category | Count |
|----------|-------|
| Public | 14 |
| User Auth | 7 |
| Admin Auth | 4 |
| Admin Dashboard | 2 |
| Admin Users | 8 |
| Admin Posts | 7 |
| Admin Categories | 5 |
| Admin Comments | 5 |
| Admin Services | 6 |
| Admin Case Studies | 5 |
| Admin Testimonials | 5 |
| Admin Brands | 6 |
| Admin Contacts | 5 |
| Admin Chatbot | 3 |
| Admin Email | 8 |
| Admin Settings | 2 |
| Admin Upload | 2 |
| AI Tools | 5 |
| **Total** | **99** |

---

*Last Updated: January 2024*
