# Smatatech Backend API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
The API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### User Registration
**POST** `/auth/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "1|xxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### User Login
**POST** `/auth/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": null
    },
    "token": "1|xxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### Admin Login
**POST** `/admin/login`

**Request Body:**
```json
{
  "email": "admin@smatatech.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "admin": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@smatatech.com",
      "role": "super_admin",
      "permissions": null
    },
    "token": "1|xxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

---

## Public Endpoints

### Get Brands
**GET** `/brands`

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": "Brand Name",
      "logo": "/storage/uploads/brand/logo.png",
      "website": "https://example.com",
      "status": "active",
      "order": 1,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Get Services
**GET** `/services`

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "Web Development",
      "slug": "web-development",
      "short_description": "Custom web solutions",
      "long_description": "Full description...",
      "icon": "icon-name",
      "image": "/storage/uploads/service/image.jpg",
      "features": ["Feature 1", "Feature 2"],
      "benefits": ["Benefit 1", "Benefit 2"],
      "process": ["Step 1", "Step 2"],
      "order": 1,
      "status": "active",
      "meta_title": "Web Development Services",
      "meta_description": "Professional web development",
      "meta_keywords": "web, development",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Get Single Service
**GET** `/services/{slug}`

### Get Blog Posts (Paginated)
**GET** `/posts?per_page=12&category_id=1&search=keyword`

**Query Parameters:**
- `per_page` (optional, default: 12)
- `category_id` (optional)
- `search` (optional)

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 50,
    "from": 1,
    "to": 12
  }
}
```

### Get Single Blog Post
**GET** `/posts/{slug}`

### Get Blog Categories
**GET** `/categories`

### Get Case Studies (Paginated)
**GET** `/case-studies?per_page=12&industry=technology`

### Get Single Case Study
**GET** `/case-studies/{slug}`

### Get Testimonials
**GET** `/testimonials?featured=true`

### Get Site Settings
**GET** `/settings`

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "site_name": "Smatatech Technologies",
    "site_description": "Leading technology solutions",
    "contact_email": "info@smatatech.com",
    "contact_phone": "+1234567890",
    "social_facebook": "https://facebook.com/smatatech",
    "logo": "/storage/uploads/logo.png"
  }
}
```

### Submit Contact Form
**POST** `/contact`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "company": "ACME Corp",
  "phone": "+1234567890",
  "project_type": "Web Development",
  "budget": "$10,000 - $50,000",
  "services": ["Web Development", "Mobile Apps"],
  "message": "I'm interested in your services..."
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Thank you for contacting us! We will get back to you soon.",
  "data": {
    "id": 1
  }
}
```

---

## Admin Endpoints (Requires Authentication)

### Dashboard Statistics
**GET** `/admin/dashboard/stats`

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "users": { "total": 100, "recent": 10 },
    "posts": { "total": 50, "published": 45, "draft": 5 },
    "services": { "total": 8, "active": 8 },
    "case_studies": { "total": 20, "published": 18 },
    "testimonials": { "total": 30, "featured": 10 },
    "brands": { "total": 15, "active": 15 },
    "contacts": { "total": 200, "unread": 5 }
  }
}
```

### Brands Management
- **GET** `/admin/brands?per_page=15&search=keyword&status=active`
- **POST** `/admin/brands`
- **GET** `/admin/brands/{id}`
- **PUT** `/admin/brands/{id}`
- **DELETE** `/admin/brands/{id}`

### Services Management
- **GET** `/admin/services?per_page=15&search=keyword&status=active`
- **POST** `/admin/services`
- **GET** `/admin/services/{id}`
- **PUT** `/admin/services/{id}`
- **DELETE** `/admin/services/{id}`

### Case Studies Management
- **GET** `/admin/case-studies?per_page=15&search=keyword&status=published`
- **POST** `/admin/case-studies`
- **GET** `/admin/case-studies/{id}`
- **PUT** `/admin/case-studies/{id}`
- **DELETE** `/admin/case-studies/{id}`

### Testimonials Management
- **GET** `/admin/testimonials?per_page=15&search=keyword&status=active`
- **POST** `/admin/testimonials`
- **GET** `/admin/testimonials/{id}`
- **PUT** `/admin/testimonials/{id}`
- **DELETE** `/admin/testimonials/{id}`

### Blog Posts Management
- **GET** `/admin/posts?per_page=15&search=keyword&status=published&category_id=1`
- **POST** `/admin/posts`
- **GET** `/admin/posts/{id}`
- **PUT** `/admin/posts/{id}`
- **DELETE** `/admin/posts/{id}`

### Blog Categories Management
- **GET** `/admin/categories?per_page=15&search=keyword`
- **POST** `/admin/categories`
- **GET** `/admin/categories/{id}`
- **PUT** `/admin/categories/{id}`
- **DELETE** `/admin/categories/{id}`

### Blog Comments Management
- **GET** `/admin/comments?per_page=15&status=pending&post_id=1`
- **PUT** `/admin/comments/{id}` (Update status)
- **DELETE** `/admin/comments/{id}`

### Users Management
- **GET** `/admin/users?per_page=15&search=keyword`
- **POST** `/admin/users`
- **GET** `/admin/users/{id}`
- **PUT** `/admin/users/{id}`
- **DELETE** `/admin/users/{id}`

### Contact Messages
- **GET** `/admin/contacts?per_page=15&read=false`
- **PUT** `/admin/contacts/{id}` (Mark as read/unread)
- **DELETE** `/admin/contacts/{id}`

### Email Settings
- **GET** `/admin/email/settings`
- **PUT** `/admin/email/settings`
- **PUT** `/admin/email/brevo`
- **POST** `/admin/email/test`

### Email Templates
- **GET** `/admin/email/templates?per_page=15`
- **POST** `/admin/email/templates`
- **PUT** `/admin/email/templates/{id}`
- **DELETE** `/admin/email/templates/{id}`

### Site Settings
- **GET** `/admin/settings`
- **PUT** `/admin/settings`

### Chatbot Configuration
- **GET** `/admin/chatbot/config`
- **PUT** `/admin/chatbot/config`
- **POST** `/admin/chatbot/toggle`

### Chatbot Training
- **GET** `/admin/chatbot/training?per_page=15&category=faq`
- **POST** `/admin/chatbot/training`
- **PUT** `/admin/chatbot/training/{id}`
- **DELETE** `/admin/chatbot/training/{id}`

### File Upload
**POST** `/admin/upload`

**Request (multipart/form-data):**
```
file: [binary]
type: image (optional, values: image, logo, favicon, blog, service, case-study, testimonial, brand)
```

**Response (201):**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "filename": "my-image-1234567890.jpg",
    "path": "uploads/image/my-image-1234567890.jpg",
    "url": "/storage/uploads/image/my-image-1234567890.jpg",
    "size": 102400,
    "mime_type": "image/jpeg"
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "An error occurred"
}
```

---

## Testing the API

### Using cURL

**Test Admin Login:**
```bash
curl -X POST http://localhost:8000/api/v1/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@smatatech.com","password":"password123"}'
```

**Test Get Brands:**
```bash
curl http://localhost:8000/api/v1/brands
```

**Test Protected Endpoint:**
```bash
curl http://localhost:8000/api/v1/admin/brands \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Default Admin Credentials
- **Email:** admin@smatatech.com
- **Password:** password123

- **Email:** admin2@smatatech.com
- **Password:** password123

---

## Notes

1. All timestamps are in ISO 8601 format
2. Pagination is available on list endpoints with `per_page` parameter
3. File uploads support JPEG, PNG, GIF, SVG, WebP formats
4. Maximum file upload size: 10MB (5MB for images)
5. All API responses follow the same structure with `success`, `message`, and `data` fields
6. CORS is configured to allow requests from `http://localhost:5173` and `http://localhost:3000`
