# Smatatech Backend System Map

> **Generated:** 2026-01-20  
> **Version:** 1.0.0  
> **Purpose:** Complete backend API and database schema documentation for frontend alignment

---

## TABLE OF CONTENTS

1. [API Endpoint Inventory](#part-1-api-endpoint-inventory)
2. [Database Schema](#part-2-database-schema)
3. [Table Relationships](#part-3-table-relationships)
4. [Authentication & Authorization](#part-4-authentication--authorization)
5. [Configuration & Environment](#part-5-configuration--environment)
6. [Data Ownership Rules](#part-6-data-ownership-rules)

---

## PART 1: API ENDPOINT INVENTORY

### Standard Response Format

All API responses follow this structure:

**Success Response:**
```json
{
  "success": true,
  "data": { },
  "message": "Optional success message"
}
```

**Paginated Response:**
```json
{
  "success": true,
  "data": [],
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

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": {}
}
```

### Required Headers (All Requests)

| Header | Value | Required |
|--------|-------|----------|
| `Accept` | `application/json` | Yes |
| `Content-Type` | `application/json` | Yes (POST/PUT/PATCH) |
| `Authorization` | `Bearer {token}` | Protected routes only |

---

## 1.1 PUBLIC ENDPOINTS

### GET /api
**Description:** API index with all endpoint information  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "name": "Smatatech API",
    "version": "1.0.0",
    "description": "RESTful API for Smatatech Technologies platform",
    "documentation": "https://api.smatatech.com.ng/api/docs",
    "health": "https://api.smatatech.com.ng/api/health",
    "status": "operational",
    "timestamp": "2026-01-20T10:00:00.000000Z",
    "endpoints": {
      "public": {},
      "auth": {},
      "admin": {},
      "ai": {}
    },
    "authentication": {
      "type": "Bearer Token",
      "header": "Authorization: Bearer {token}",
      "tokenLifetime": {
        "user": "7 days",
        "admin": "1 day"
      }
    },
    "headers": {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "Authorization": "Bearer {token} (for protected endpoints)"
    },
    "rateLimit": {
      "requests": 60,
      "perMinutes": 1
    }
  }
}
```

---

### GET /api/docs
**Description:** Full API documentation  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "title": "Smatatech API Documentation",
    "version": "1.0.0",
    "baseUrl": "https://api.smatatech.com.ng/api",
    "description": "Complete API documentation for developers",
    "lastUpdated": "2026-01-20",
    "authentication": {},
    "headers": {},
    "responseFormat": {},
    "errorCodes": {},
    "endpoints": {},
    "examples": {},
    "integration": {}
  }
}
```

---

### GET /api/health
**Description:** Health check endpoint  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2026-01-20T10:00:00.000000Z",
    "services": {
      "database": "connected",
      "storage": "accessible"
    }
  }
}
```

---

### GET /api/settings
**Description:** Get public site settings  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "siteName": "Smatatech Technologies",
    "siteTagline": "AI-Powered Digital Solutions",
    "siteDescription": "Digital Solutions Company",
    "logo": {
      "light": null,
      "dark": null,
      "favicon": "/favicon.ico"
    },
    "contact": {
      "email": "hello@smatatech.com.ng",
      "phone": "+234 XXX XXX XXXX",
      "whatsapp": null,
      "address": "Lagos, Nigeria",
      "city": "",
      "country": ""
    },
    "contactEmail": "hello@smatatech.com.ng",
    "contactPhone": "+234 XXX XXX XXXX",
    "address": "Lagos, Nigeria",
    "socialLinks": {
      "facebook": null,
      "twitter": null,
      "linkedin": null,
      "instagram": null,
      "youtube": null,
      "github": null,
      "whatsapp": null
    },
    "seo": {
      "defaultTitle": "Smatatech",
      "titleSeparator": " | ",
      "defaultDescription": "",
      "defaultKeywords": [],
      "ogImage": null
    },
    "footer": {
      "copyrightText": "Â© 2026 Smatatech Technologies. All rights reserved.",
      "showSocialLinks": true
    },
    "heroStats": [
      {"value": "150+", "label": "Projects Delivered"},
      {"value": "50+", "label": "Happy Clients"},
      {"value": "5+", "label": "Years Experience"}
    ],
    "features": {
      "chatbotEnabled": false,
      "blogEnabled": true,
      "newsletterEnabled": true
    }
  }
}
```

---

### GET /api/services
**Description:** List all published services  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Query Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| None | - | - | - |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Web Development",
      "slug": "web-development",
      "shortDescription": "Custom web applications built with modern technologies.",
      "fullDescription": "Full description text...",
      "icon": "code",
      "image": "https://api.smatatech.com.ng/storage/services/image.jpg",
      "features": ["Feature 1", "Feature 2"],
      "status": "published",
      "order": 1,
      "createdAt": "2026-01-20T10:00:00.000000Z",
      "updatedAt": "2026-01-20T10:00:00.000000Z"
    }
  ]
}
```

---

### GET /api/services/{slug}
**Description:** Get service details by slug  
**Auth Required:** No  
**Middleware:** `throttle:api`

**URL Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| slug | string | Yes | Service URL slug |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Web Development",
    "slug": "web-development",
    "shortDescription": "Custom web applications built with modern technologies.",
    "fullDescription": "Full description text...",
    "icon": "code",
    "image": "https://api.smatatech.com.ng/storage/services/image.jpg",
    "features": ["Feature 1", "Feature 2"],
    "benefits": [
      {"title": "Scalable", "description": "Built to scale"}
    ],
    "processSteps": [
      {"step": 1, "title": "Discovery", "description": "We learn about your needs"}
    ],
    "seo": {
      "metaTitle": "Web Development Services",
      "metaDescription": "Professional web development services",
      "ogImage": "https://api.smatatech.com.ng/storage/og/web-dev.jpg"
    },
    "status": "published",
    "order": 1,
    "createdAt": "2026-01-20T10:00:00.000000Z",
    "updatedAt": "2026-01-20T10:00:00.000000Z"
  }
}
```

**Response (404):**
```json
{
  "success": false,
  "message": "Service not found"
}
```

---

### GET /api/posts
**Description:** List all published blog posts (paginated)  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page (max 50) |
| page | integer | No | 1 | Page number |
| category | string | No | - | Filter by category slug |
| search | string | No | - | Search in title/content |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Building Scalable Laravel Applications",
      "slug": "building-scalable-laravel-applications",
      "excerpt": "Learn the best practices...",
      "featuredImage": "https://api.smatatech.com.ng/storage/posts/image.jpg",
      "category": {
        "id": "uuid",
        "name": "Web Development",
        "slug": "web-development"
      },
      "author": {
        "name": "Admin User",
        "avatar": "https://api.smatatech.com.ng/storage/avatars/admin.jpg"
      },
      "readTime": "5 min read",
      "isFeatured": false,
      "publishedAt": "2026-01-15T10:00:00.000000Z"
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 5,
    "perPage": 15,
    "total": 75,
    "from": 1,
    "to": 15
  }
}
```

---

### GET /api/posts/{slug}
**Description:** Get blog post details by slug  
**Auth Required:** No  
**Middleware:** `throttle:api`

**URL Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| slug | string | Yes | Post URL slug |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Building Scalable Laravel Applications",
    "slug": "building-scalable-laravel-applications",
    "excerpt": "Learn the best practices...",
    "content": "<p>Full HTML content...</p>",
    "featuredImage": "https://api.smatatech.com.ng/storage/posts/image.jpg",
    "category": {
      "id": "uuid",
      "name": "Web Development",
      "slug": "web-development"
    },
    "author": {
      "name": "Admin User",
      "role": "Senior Developer",
      "avatar": "https://api.smatatech.com.ng/storage/avatars/admin.jpg",
      "bio": "Author bio text..."
    },
    "tags": ["laravel", "php", "backend"],
    "readTime": "5 min read",
    "seo": {
      "metaTitle": "Building Scalable Laravel Applications",
      "metaDescription": "Learn the best practices...",
      "ogImage": "https://api.smatatech.com.ng/storage/posts/image.jpg"
    },
    "commentsEnabled": true,
    "publishedAt": "2026-01-15T10:00:00.000000Z",
    "createdAt": "2026-01-15T09:00:00.000000Z",
    "updatedAt": "2026-01-15T10:00:00.000000Z"
  }
}
```

---

### GET /api/posts/{slug}/related
**Description:** Get related posts by category/tags  
**Auth Required:** No  
**Middleware:** `throttle:api`

**URL Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| slug | string | Yes | Post URL slug |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Related Post Title",
      "slug": "related-post-slug",
      "excerpt": "Related post excerpt...",
      "featuredImage": "https://api.smatatech.com.ng/storage/posts/image.jpg",
      "publishedAt": "2026-01-14T10:00:00.000000Z"
    }
  ]
}
```

---

### GET /api/categories
**Description:** List all active categories  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Web Development",
      "slug": "web-development",
      "description": "Articles about web development",
      "postCount": 25,
      "status": "active"
    }
  ]
}
```

---

### GET /api/case-studies
**Description:** List all published case studies (paginated)  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| per_page | integer | No | 10 | Items per page (max 50) |
| page | integer | No | 1 | Page number |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "E-Commerce Platform Redesign",
      "slug": "ecommerce-platform-redesign",
      "clientName": "TechRetail Inc.",
      "industry": "E-Commerce",
      "featuredImage": "https://api.smatatech.com.ng/storage/case-studies/image.jpg",
      "shortDescription": "Complete redesign resulting in 150% increase in conversions.",
      "highlightStat": {
        "value": "150%",
        "label": "Conversion Increase"
      },
      "status": "published",
      "publishDate": "2026-01-01",
      "createdAt": "2026-01-01T10:00:00.000000Z"
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 2,
    "perPage": 10,
    "total": 15,
    "from": 1,
    "to": 10
  }
}
```

---

### GET /api/case-studies/{slug}
**Description:** Get case study details by slug  
**Auth Required:** No  
**Middleware:** `throttle:api`

**URL Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| slug | string | Yes | Case study URL slug |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "E-Commerce Platform Redesign",
    "slug": "ecommerce-platform-redesign",
    "clientName": "TechRetail Inc.",
    "industry": "E-Commerce",
    "duration": "6 months",
    "year": "2025",
    "featuredImage": "https://api.smatatech.com.ng/storage/case-studies/image.jpg",
    "shortDescription": "Complete redesign resulting in 150% increase in conversions.",
    "challenge": {
      "overview": "The client's existing platform was outdated...",
      "points": ["Slow load times", "Poor mobile experience", "High cart abandonment"]
    },
    "solution": {
      "overview": "We redesigned the entire user experience...",
      "points": ["Mobile-first approach", "New Laravel backend", "Optimized checkout"]
    },
    "results": [
      {"value": "150%", "label": "Conversion Increase"},
      {"value": "60%", "label": "Cart Abandonment Reduction"},
      {"value": "55%", "label": "Mobile Sales Increase"}
    ],
    "processSteps": [
      {"step": 1, "title": "Discovery", "description": "Understanding requirements"}
    ],
    "technologies": ["Laravel", "Vue.js", "Redis", "AWS"],
    "testimonial": {
      "quote": "Smatatech transformed our platform...",
      "author": "Sarah Johnson",
      "role": "CEO, TechRetail Inc."
    },
    "gallery": [
      {"type": "image", "url": "https://api.smatatech.com.ng/storage/gallery/1.jpg", "caption": "Homepage"}
    ],
    "seo": {
      "metaTitle": "E-Commerce Platform Redesign Case Study",
      "metaDescription": "See how we helped TechRetail increase conversions by 150%",
      "ogImage": "https://api.smatatech.com.ng/storage/case-studies/image.jpg"
    },
    "status": "published",
    "publishDate": "2026-01-01",
    "createdAt": "2026-01-01T10:00:00.000000Z",
    "updatedAt": "2026-01-01T10:00:00.000000Z"
  }
}
```

---

### GET /api/case-studies/{slug}/related
**Description:** Get related case studies by industry  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Related Case Study",
      "slug": "related-case-study",
      "clientName": "Another Client",
      "industry": "E-Commerce",
      "featuredImage": "https://api.smatatech.com.ng/storage/case-studies/image2.jpg",
      "shortDescription": "Another success story...",
      "highlightStat": null,
      "status": "published",
      "publishDate": "2025-12-01",
      "createdAt": "2025-12-01T10:00:00.000000Z"
    }
  ]
}
```

---

### GET /api/testimonials
**Description:** List all published testimonials  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| featured | boolean | No | - | Filter featured only |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "clientName": "Sarah Johnson",
      "company": "TechRetail Inc.",
      "role": "CEO",
      "testimonialText": "Smatatech transformed our platform completely...",
      "avatar": "https://api.smatatech.com.ng/storage/avatars/sarah.jpg",
      "rating": 5,
      "projectType": "E-Commerce",
      "isFeatured": true,
      "status": "published",
      "createdAt": "2026-01-01T10:00:00.000000Z",
      "updatedAt": "2026-01-01T10:00:00.000000Z"
    }
  ]
}
```

---

### GET /api/brands
**Description:** List all active brands/clients  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "TechRetail Inc.",
      "logo": "https://api.smatatech.com.ng/storage/brands/techretail.png",
      "website": "https://techretail.example.com",
      "websiteUrl": "https://techretail.example.com",
      "status": "active",
      "order": 1,
      "createdAt": "2026-01-01T10:00:00.000000Z",
      "updatedAt": "2026-01-01T10:00:00.000000Z"
    }
  ]
}
```

---

### POST /api/contact
**Description:** Submit contact form  
**Auth Required:** No  
**Middleware:** `throttle:contact` (5 requests/minute)

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "company": "Acme Inc.",
  "phone": "+1234567890",
  "projectType": "Web Development",
  "budget": 10000,
  "services": ["web-development", "api-development"],
  "message": "I need help building a web application...",
  "metadata": {
    "source": "https://smatatech.com.ng/contact",
    "referrer": "https://google.com",
    "utmSource": "google",
    "utmMedium": "cpc",
    "utmCampaign": "spring-promo"
  }
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| email | required, email, max:255 |
| company | nullable, string, max:255 |
| phone | nullable, string, max:50 |
| projectType | nullable, string, max:100 |
| budget | nullable, numeric |
| services | nullable, array |
| services.* | string |
| message | required, string, max:5000 |
| metadata | nullable, array |
| metadata.source | nullable, string, max:500 |
| metadata.referrer | nullable, string, max:500 |
| metadata.utmSource | nullable, string, max:255 |
| metadata.utmMedium | nullable, string, max:255 |
| metadata.utmCampaign | nullable, string, max:255 |

**Response (201):**
```json
{
  "success": true,
  "data": null,
  "message": "Thank you for your message. We will get back to you soon."
}
```

**Response (422):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "message": ["The message field is required."]
  }
}
```

---

### GET /api/chatbot/config
**Description:** Get public chatbot configuration  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "isEnabled": true,
    "greetingMessage": "Hello! How can I help you today?",
    "suggestedQuestions": [
      "What services do you offer?",
      "How can I get a quote?",
      "Tell me about your company"
    ],
    "companyInfo": {
      "name": "Smatatech",
      "services": ["Web Development", "Mobile Apps", "AI Integration"],
      "contactEmail": "hello@smatatech.com.ng",
      "contactPhone": "+234 XXX XXX XXXX"
    }
  }
}
```

---

### POST /api/chat
**Description:** Send chat message (server-side AI proxy)  
**Auth Required:** No  
**Middleware:** `throttle:api` + rate limiting (10 requests/minute per IP)

**Request Body:**
```json
{
  "message": "What services do you offer?",
  "conversationId": "uuid-optional",
  "context": {
    "page": "/services",
    "previousMessages": [
      {"role": "user", "content": "Hello"},
      {"role": "assistant", "content": "Hi! How can I help?"}
    ]
  }
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| message | required, string, max:1000 |
| conversationId | nullable, string, max:36 |
| context | nullable, array |
| context.page | nullable, string, max:500 |
| context.previousMessages | nullable, array, max:10 |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "message": "We offer a range of services including Web Development, Mobile App Development, UI/UX Design, Cloud Solutions, API Development, and AI Integration. Would you like more details about any specific service?",
    "conversationId": "uuid"
  }
}
```

**Response (429):**
```json
{
  "success": false,
  "message": "Too many requests. Please wait 30 seconds."
}
```

**Response (503):**
```json
{
  "success": false,
  "message": "Chat service is not configured."
}
```

---

### POST /api/newsletter/subscribe
**Description:** Subscribe to newsletter  
**Auth Required:** No  
**Middleware:** `throttle:api` + rate limiting (5 requests/minute per IP)

**Request Body:**
```json
{
  "email": "subscriber@example.com",
  "consent": true
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| email | required, email, max:255 |
| consent | required, boolean, accepted |

**Response (201):**
```json
{
  "success": true,
  "data": null,
  "message": "Successfully subscribed to our newsletter!"
}
```

**Response (200) - Already subscribed:**
```json
{
  "success": true,
  "data": null,
  "message": "You are already subscribed to our newsletter."
}
```

---

### POST /api/newsletter/unsubscribe
**Description:** Unsubscribe from newsletter  
**Auth Required:** No  
**Middleware:** `throttle:api`

**Request Body:**
```json
{
  "email": "subscriber@example.com"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "You have been unsubscribed from our newsletter."
}
```

**Response (404):**
```json
{
  "success": false,
  "message": "Email not found in our newsletter list."
}
```

---

### POST /api/inquiries
**Description:** Submit service inquiry  
**Auth Required:** No  
**Middleware:** `throttle:api` + rate limiting (5 requests/minute per IP)

**Request Body:**
```json
{
  "serviceSlug": "web-development",
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "company": "Acme Inc.",
  "budgetRange": "$10,000 - $25,000",
  "timeline": "1-3 months",
  "message": "I need a custom web application for my business..."
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| serviceSlug | required, string, max:255 |
| name | required, string, max:255 |
| email | required, email, max:255 |
| phone | nullable, string, max:50 |
| company | nullable, string, max:255 |
| budgetRange | nullable, string, max:50 |
| timeline | nullable, string, max:50 |
| message | required, string, max:5000 |

**Response (201):**
```json
{
  "success": true,
  "data": null,
  "message": "Your inquiry has been submitted successfully. We will get back to you soon!"
}
```

---

## 1.2 USER AUTHENTICATION ENDPOINTS

### POST /api/auth/register
**Description:** Register new user account  
**Auth Required:** No  
**Middleware:** `throttle:auth`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| name | required, string, max:255 |
| email | required, string, email, max:255, unique:users |
| password | required, string, min:8, confirmed |

**Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "createdAt": "2026-01-20T10:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456",
    "expiresAt": "2026-01-27T10:00:00.000000Z"
  },
  "message": "Registration successful"
}
```

---

### POST /api/auth/login
**Description:** User login  
**Auth Required:** No  
**Middleware:** `throttle:auth`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "securepassword123"
}
```

**Validation Rules:**
| Field | Rules |
|-------|-------|
| email | required, string, email |
| password | required, string |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "createdAt": "2026-01-20T10:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456",
    "expiresAt": "2026-01-27T10:00:00.000000Z"
  },
  "message": "Login successful"
}
```

**Response (401):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

---

### POST /api/auth/logout
**Description:** Logout user and revoke token  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Headers:**
```
Authorization: Bearer {user_token}
```

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Logged out successfully"
}
```

---

### GET /api/auth/me
**Description:** Get current authenticated user profile  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Headers:**
```
Authorization: Bearer {user_token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "avatar": "https://api.smatatech.com.ng/storage/avatars/user.jpg",
    "emailVerifiedAt": "2026-01-20T10:00:00.000000Z",
    "createdAt": "2026-01-20T10:00:00.000000Z"
  }
}
```

---

### POST /api/auth/refresh
**Description:** Refresh authentication token  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "2|newtoken123456789",
    "expiresAt": "2026-01-27T10:00:00.000000Z"
  },
  "message": "Token refreshed successfully"
}
```

---

### POST /api/auth/forgot-password
**Description:** Request password reset email  
**Auth Required:** No  
**Middleware:** `throttle:auth`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Password reset link sent to your email"
}
```

---

### POST /api/auth/reset-password
**Description:** Reset password with token  
**Auth Required:** No  
**Middleware:** `throttle:auth`

**Request Body:**
```json
{
  "token": "reset_token_from_email",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Password reset successfully"
}
```

---

## 1.3 AI TOOLS ENDPOINTS

### GET /api/ai/tools
**Description:** List available AI tools  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Content Generator",
      "slug": "content-generator",
      "description": "Generate blog posts and articles",
      "creditCost": 5,
      "isEnabled": true
    }
  ]
}
```

---

### GET /api/ai/credits
**Description:** Get user's credit balance  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "balance": 100,
    "lastUpdated": "2026-01-20T10:00:00.000000Z"
  }
}
```

---

### POST /api/ai/credits/purchase
**Description:** Purchase credits  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Response (501):**
```json
{
  "success": false,
  "message": "Credit purchase is not yet available"
}
```

---

### GET /api/ai/usage
**Description:** Get AI usage history  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page (max 100) |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "toolName": "Content Generator",
      "creditsUsed": 5,
      "createdAt": "2026-01-20T10:00:00.000000Z"
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 1,
    "perPage": 15,
    "total": 5,
    "from": 1,
    "to": 5
  }
}
```

---

### POST /api/ai/tools/{id}/execute
**Description:** Execute an AI tool  
**Auth Required:** Yes (User)  
**Middleware:** `auth:sanctum`

**URL Parameters:**
| Param | Type | Required | Description |
|-------|------|----------|-------------|
| id | uuid | Yes | AI tool ID |

**Request Body:**
```json
{
  "input": "Write a blog post about Laravel",
  "options": {
    "tone": "professional",
    "length": "medium"
  }
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "result": "Generated content...",
    "creditsUsed": 5,
    "remainingCredits": 95
  }
}
```

**Response (402):**
```json
{
  "success": false,
  "message": "Insufficient credits"
}
```

---

## 1.4 ADMIN AUTHENTICATION ENDPOINTS

### POST /api/admin/login
**Description:** Admin login  
**Auth Required:** No  
**Middleware:** `throttle:admin`

**Request Body:**
```json
{
  "email": "admin@smatatech.com.ng",
  "password": "Admin@123456"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "Super Admin",
      "email": "admin@smatatech.com.ng",
      "avatar": "https://api.smatatech.com.ng/storage/avatars/admin.jpg",
      "roleTitle": "Administrator",
      "bio": null,
      "role": "super_admin",
      "permissions": ["manage_users", "manage_posts", "manage_settings"],
      "createdAt": "2026-01-20T10:00:00.000000Z",
      "lastLoginAt": "2026-01-20T10:00:00.000000Z"
    },
    "token": "1|admintoken123456789",
    "expiresAt": "2026-01-21T10:00:00.000000Z"
  },
  "message": "Login successful"
}
```

---

### POST /api/admin/logout
**Description:** Admin logout  
**Auth Required:** Yes (Admin)  
**Middleware:** `auth:admin`

**Response (200):**
```json
{
  "success": true,
  "data": null,
  "message": "Logged out successfully"
}
```

---

### GET /api/admin/me
**Description:** Get current admin profile  
**Auth Required:** Yes (Admin)  
**Middleware:** `auth:admin`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "Super Admin",
    "email": "admin@smatatech.com.ng",
    "avatar": "https://api.smatatech.com.ng/storage/avatars/admin.jpg",
    "roleTitle": "Administrator",
    "bio": null,
    "role": "super_admin",
    "permissions": ["manage_users", "manage_posts", "manage_settings"],
    "createdAt": "2026-01-20T10:00:00.000000Z",
    "lastLoginAt": "2026-01-20T10:00:00.000000Z"
  }
}
```

---

### POST /api/admin/refresh
**Description:** Refresh admin token  
**Auth Required:** Yes (Admin)  
**Middleware:** `auth:admin`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "2|newadmintoken123456789",
    "expiresAt": "2026-01-21T10:00:00.000000Z"
  }
}
```

---

## 1.5 ADMIN DASHBOARD ENDPOINTS

### GET /api/admin/dashboard/stats
**Description:** Get dashboard statistics  
**Auth Required:** Yes (Admin)  
**Required Role:** viewer+  
**Middleware:** `auth:admin`, `role:viewer`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "totalUsers": 150,
    "totalPosts": 75,
    "totalServices": 6,
    "totalContacts": 45,
    "totalCaseStudies": 15,
    "unreadContacts": 5,
    "recentActivity": []
  }
}
```

---

### GET /api/admin/dashboard/activity
**Description:** Get recent activity logs  
**Auth Required:** Yes (Admin)  
**Required Role:** viewer+  
**Middleware:** `auth:admin`, `role:viewer`

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| per_page | integer | No | 20 | Items per page |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "type": "post_created",
      "title": "New blog post created",
      "description": "Post 'Building Laravel Apps' was created",
      "actorName": "Admin User",
      "createdAt": "2026-01-20T10:00:00.000000Z"
    }
  ]
}
```



---

## 1.6 ADMIN USER MANAGEMENT ENDPOINTS

### GET /api/admin/users
**Description:** List all users  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+  
**Middleware:** `auth:admin`, `role:admin`

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page |
| search | string | No | - | Search by name/email |
| status | string | No | - | Filter by status |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "status": "active",
      "role": "user",
      "createdAt": "2026-01-20T10:00:00.000000Z"
    }
  ],
  "meta": {
    "currentPage": 1,
    "lastPage": 10,
    "perPage": 15,
    "total": 150
  }
}
```

---

### GET /api/admin/users/{id}
**Description:** Get user details  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/users
**Description:** Create new user  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "securepassword123"
}
```

---

### PUT /api/admin/users/{id}
**Description:** Update user  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/users/{id}/activate
**Description:** Activate user account  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/users/{id}/deactivate
**Description:** Deactivate user account  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### DELETE /api/admin/users/{id}
**Description:** Delete user  
**Auth Required:** Yes (Admin)  
**Required Role:** super_admin

---

### POST /api/admin/users/{id}/role
**Description:** Assign role to user  
**Auth Required:** Yes (Admin)  
**Required Role:** super_admin

---

## 1.7 ADMIN BLOG POSTS ENDPOINTS

### GET /api/admin/posts
**Description:** List all posts (including drafts)  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page |
| status | string | No | - | Filter by status |
| category_id | uuid | No | - | Filter by category |
| search | string | No | - | Search in title |

---

### GET /api/admin/posts/{id}
**Description:** Get post details by ID  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/posts
**Description:** Create new post  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

**Request Body:**
```json
{
  "title": "New Blog Post",
  "slug": "new-blog-post",
  "excerpt": "Post excerpt...",
  "content": "<p>Full content...</p>",
  "categoryId": "uuid",
  "featuredImage": "/storage/posts/image.jpg",
  "tags": ["tag1", "tag2"],
  "isFeatured": false,
  "commentsEnabled": true,
  "status": "draft",
  "metaTitle": "SEO Title",
  "metaDescription": "SEO Description"
}
```

---

### PUT /api/admin/posts/{id}
**Description:** Update post  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/posts/{id}/publish
**Description:** Publish post  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/posts/{id}/unpublish
**Description:** Unpublish post  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### DELETE /api/admin/posts/{id}
**Description:** Delete post  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.8 ADMIN CATEGORIES ENDPOINTS

### GET /api/admin/categories
**Description:** List all categories  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### GET /api/admin/categories/{id}
**Description:** Get category details  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/categories
**Description:** Create category  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

**Request Body:**
```json
{
  "name": "New Category",
  "slug": "new-category",
  "description": "Category description",
  "status": "active"
}
```

---

### PUT /api/admin/categories/{id}
**Description:** Update category  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### DELETE /api/admin/categories/{id}
**Description:** Delete category  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.9 ADMIN COMMENTS ENDPOINTS

### GET /api/admin/comments
**Description:** List all comments  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### GET /api/admin/comments/{id}
**Description:** Get comment details  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/comments/{id}/approve
**Description:** Approve comment  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/comments/{id}/reject
**Description:** Reject comment  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### DELETE /api/admin/comments/{id}
**Description:** Delete comment  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.10 ADMIN SERVICES ENDPOINTS

### GET /api/admin/services
**Description:** List all services  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### GET /api/admin/services/{id}
**Description:** Get service details  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/services
**Description:** Create service  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/services/{id}
**Description:** Update service  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### DELETE /api/admin/services/{id}
**Description:** Delete service  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/services/reorder
**Description:** Reorder services  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

## 1.11 ADMIN CASE STUDIES ENDPOINTS

### GET /api/admin/case-studies
**Description:** List all case studies  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### GET /api/admin/case-studies/{id}
**Description:** Get case study details  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/case-studies
**Description:** Create case study  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/case-studies/{id}
**Description:** Update case study  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### DELETE /api/admin/case-studies/{id}
**Description:** Delete case study  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.12 ADMIN TESTIMONIALS ENDPOINTS

### GET /api/admin/testimonials
**Description:** List all testimonials  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### GET /api/admin/testimonials/{id}
**Description:** Get testimonial details  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/testimonials
**Description:** Create testimonial  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/testimonials/{id}
**Description:** Update testimonial  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### DELETE /api/admin/testimonials/{id}
**Description:** Delete testimonial  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.13 ADMIN BRANDS ENDPOINTS

### GET /api/admin/brands
**Description:** List all brands  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### GET /api/admin/brands/{id}
**Description:** Get brand details  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### POST /api/admin/brands
**Description:** Create brand  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/brands/{id}
**Description:** Update brand  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

### DELETE /api/admin/brands/{id}
**Description:** Delete brand  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/brands/reorder
**Description:** Reorder brands  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

## 1.14 ADMIN CONTACTS ENDPOINTS

### GET /api/admin/contacts
**Description:** List all contact submissions  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

**Query Parameters:**
| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| status | string | No | - | Filter by status (unread/read) |

---

### GET /api/admin/contacts/{id}
**Description:** Get contact details  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/contacts/{id}/read
**Description:** Mark contact as read  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/contacts/{id}/unread
**Description:** Mark contact as unread  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### DELETE /api/admin/contacts/{id}
**Description:** Delete contact  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.15 ADMIN CHATBOT CONFIGURATION ENDPOINTS

### GET /api/admin/chatbot/config
**Description:** Get chatbot configuration  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/chatbot/config
**Description:** Update chatbot configuration  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### POST /api/admin/chatbot/toggle
**Description:** Toggle chatbot on/off  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.16 ADMIN EMAIL SETTINGS ENDPOINTS

### GET /api/admin/email/settings
**Description:** Get email settings  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/email/settings
**Description:** Update email settings  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### GET /api/admin/email/templates
**Description:** List email templates  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### GET /api/admin/email/templates/{id}
**Description:** Get email template details  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/email/templates/{id}
**Description:** Update email template  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.17 ADMIN BREVO CONFIGURATION ENDPOINTS

### GET /api/admin/email/brevo
**Description:** Get Brevo configuration  
**Auth Required:** Yes (Admin)  
**Required Role:** super_admin

---

### PUT /api/admin/email/brevo
**Description:** Update Brevo configuration  
**Auth Required:** Yes (Admin)  
**Required Role:** super_admin

---

### POST /api/admin/email/brevo/test
**Description:** Test Brevo configuration  
**Auth Required:** Yes (Admin)  
**Required Role:** super_admin

---

## 1.18 ADMIN SITE SETTINGS ENDPOINTS

### GET /api/admin/settings
**Description:** Get all site settings  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

### PUT /api/admin/settings
**Description:** Update site settings  
**Auth Required:** Yes (Admin)  
**Required Role:** admin+

---

## 1.19 ADMIN FILE UPLOAD ENDPOINTS

### POST /api/admin/upload
**Description:** Upload file  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

**Request:** `multipart/form-data`

---

### DELETE /api/admin/upload
**Description:** Delete uploaded file  
**Auth Required:** Yes (Admin)  
**Required Role:** editor+

---

## PART 2: DATABASE TABLES

| Table | Description |
|-------|-------------|
| users | Public users |
| admins | Admin users |
| services | Company services |
| posts | Blog posts |
| categories | Blog categories |
| comments | Post comments |
| case_studies | Portfolio case studies |
| testimonials | Client testimonials |
| brands | Client logos/brands |
| contacts | Contact form submissions |
| site_settings | Site configuration |
| chatbot_configs | Chatbot settings |
| chatbot_conversations | Chat history |
| email_settings | SMTP configuration |
| email_templates | Email templates |
| brevo_config | Brevo integration |
| newsletter_subscribers | Newsletter list |
| service_inquiries | Service inquiries |
| activity_logs | Admin activity logs |
| roles | User roles |
| permissions | User permissions |

---

## PART 3: ROLE HIERARCHY

| Role | Level | Permissions |
|------|-------|-------------|
| viewer | 1 | View dashboard, view content |
| editor | 2 | viewer + create/edit posts, services |
| admin | 3 | editor + manage users, contacts, settings |
| super_admin | 4 | admin + delete users, manage Brevo |

---

## PART 4: RATE LIMITS

| Group | Limit |
|-------|-------|
| api | 60 requests/minute |
| auth | 5 requests/minute |
| admin | 60 requests/minute |
| contact | 5 requests/minute |

---

## ENDPOINT SUMMARY

| Category | Count |
|----------|-------|
| Public Endpoints | 21 |
| User Auth Endpoints | 7 |
| AI Tools Endpoints | 5 |
| Admin Endpoints | 58 |
| **Total** | **91** |

---

> **Last Updated:** 2026-01-21  
> **Version:** 1.1.0
