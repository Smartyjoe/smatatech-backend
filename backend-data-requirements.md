# Backend Data Requirements Document

## Smatatech Frontend ↔ Backend Data Audit

**Generated:** 2026-01-21  
**Purpose:** Document all missing, incomplete, or poorly structured data points required by the frontend.

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Services Endpoint](#1-services-endpoint)
3. [Case Studies Endpoint](#2-case-studies-endpoint)
4. [Blog Posts Endpoint](#3-blog-posts-endpoint)
5. [Testimonials Endpoint](#4-testimonials-endpoint)
6. [Brands Endpoint](#5-brands-endpoint)
7. [Site Settings Endpoint](#6-site-settings-endpoint)
8. [Contact Form Endpoint](#7-contact-form-endpoint)
9. [AI Chatbot Configuration](#8-ai-chatbot-configuration)
10. [New Endpoints Required](#9-new-endpoints-required)
11. [Environment Variables](#10-environment-variables)

---

## Executive Summary

### Critical Issues Identified

| Priority | Endpoint | Issue | Impact |
|----------|----------|-------|--------|
| 🔴 HIGH | `/api/services/{slug}` | Missing `features`, `benefits`, `processSteps` | Service detail pages render with placeholder data |
| 🔴 HIGH | `/api/case-studies/{slug}` | Missing `technologies`, `testimonial`, `gallery`, `duration`, `results` array | Case study detail pages lack key sections |
| 🟡 MEDIUM | `/api/posts/{slug}` | Missing `author.role`, `tags`, `relatedPosts` | Blog post pages missing author info and related content |
| 🟡 MEDIUM | `/api/settings` | Missing `logo`, `favicon`, `seoDefaults`, `footerContent` | Global layout elements use hardcoded values |
| 🟢 LOW | `/api/testimonials` | Missing `rating` field | Star ratings default to 5 |

---

## 1. Services Endpoint

### Current Response (GET /api/services)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "string",
      "slug": "string",
      "shortDescription": "string",
      "fullDescription": "string",
      "icon": "string",
      "image": "string|null",
      "status": "published|draft",
      "order": "integer",
      "createdAt": "datetime",
      "updatedAt": "datetime"
    }
  ]
}
```

### Required Response (GET /api/services)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "string",
      "slug": "string",
      "shortDescription": "string",
      "fullDescription": "string",
      "icon": "string",
      "image": "string|null",
      "features": ["string"],
      "status": "published|draft",
      "order": "integer",
      "createdAt": "datetime",
      "updatedAt": "datetime"
    }
  ]
}
```

### Required Response (GET /api/services/{slug})

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "string (required)",
    "slug": "string (required)",
    "shortDescription": "string (required) - Used as subtitle",
    "fullDescription": "string (required) - Main description paragraph",
    "icon": "string (required) - Icon name: Globe|Bot|Smartphone|Workflow|Palette|TrendingUp|Brain|Cloud",
    "image": "string|null - URL to service hero image",
    "features": [
      "string - Feature 1",
      "string - Feature 2",
      "... (array of 6-10 features)"
    ],
    "benefits": [
      "string - Benefit 1",
      "string - Benefit 2",
      "... (array of 4-6 benefits)"
    ],
    "processSteps": [
      {
        "step": "01",
        "title": "string (required)",
        "description": "string (required)"
      },
      {
        "step": "02",
        "title": "string",
        "description": "string"
      }
    ],
    "seo": {
      "metaTitle": "string|null",
      "metaDescription": "string|null",
      "ogImage": "string|null"
    },
    "status": "published|draft",
    "order": "integer",
    "createdAt": "datetime",
    "updatedAt": "datetime"
  }
}
```

### New Fields to Add (Services Table)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `features` | JSON/TEXT | Yes | Array of feature strings (6-10 items) |
| `benefits` | JSON/TEXT | Yes | Array of benefit strings (4-6 items) |
| `process_steps` | JSON/TEXT | Yes | Array of {step, title, description} objects |
| `meta_title` | VARCHAR(255) | No | SEO meta title |
| `meta_description` | TEXT | No | SEO meta description |
| `og_image` | VARCHAR(500) | No | Open Graph image URL |

### Migration SQL

```sql
ALTER TABLE services
ADD COLUMN features JSON DEFAULT NULL COMMENT 'Array of feature strings',
ADD COLUMN benefits JSON DEFAULT NULL COMMENT 'Array of benefit strings',
ADD COLUMN process_steps JSON DEFAULT NULL COMMENT 'Array of process step objects',
ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL,
ADD COLUMN meta_description TEXT DEFAULT NULL,
ADD COLUMN og_image VARCHAR(500) DEFAULT NULL;
```

---

## 2. Case Studies Endpoint

### Current Response (GET /api/case-studies)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "string",
      "slug": "string",
      "clientName": "string",
      "industry": "string",
      "featuredImage": "string|null",
      "problem": "string",
      "solution": "string",
      "result": "string",
      "status": "published|draft",
      "publishDate": "date|null",
      "createdAt": "datetime",
      "updatedAt": "datetime"
    }
  ]
}
```

### Required Response (GET /api/case-studies - List)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "string",
      "slug": "string",
      "clientName": "string",
      "industry": "string",
      "featuredImage": "string|null",
      "shortDescription": "string (NEW) - 150 char summary for cards",
      "highlightStat": {
        "value": "string (e.g., '300%', '$500K', '10K+')",
        "label": "string (e.g., 'Faster Decisions', 'Annual Savings')"
      },
      "status": "published|draft",
      "publishDate": "date|null",
      "createdAt": "datetime"
    }
  ],
  "meta": {
    "currentPage": "integer",
    "lastPage": "integer",
    "perPage": "integer",
    "total": "integer"
  }
}
```

### Required Response (GET /api/case-studies/{slug} - Detail)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "string (required)",
    "slug": "string (required)",
    "clientName": "string (required)",
    "industry": "string (required)",
    "duration": "string (NEW) - e.g., '4 months', '12 weeks'",
    "year": "string (NEW) - e.g., '2024'",
    "featuredImage": "string|null - Hero image URL",
    "shortDescription": "string (NEW) - Brief project summary",
    
    "challenge": {
      "overview": "string (required) - Paragraph describing the challenge",
      "points": ["string - Challenge point 1", "string - Challenge point 2"]
    },
    
    "solution": {
      "overview": "string (required) - Paragraph describing the solution",
      "points": ["string - Solution point 1", "string - Solution point 2"]
    },
    
    "results": [
      {
        "value": "string (required) - e.g., '300%'",
        "label": "string (required) - e.g., 'Faster Decision Making'",
        "description": "string - Extended description"
      }
    ],
    
    "processSteps": [
      {
        "title": "string (required)",
        "description": "string (required)"
      }
    ],
    
    "technologies": ["string - Tech 1", "string - Tech 2"],
    
    "testimonial": {
      "quote": "string (required)",
      "author": "string (required)",
      "role": "string (required) - e.g., 'VP of Analytics, NovaTech Inc.'"
    },
    
    "gallery": [
      {
        "type": "image|video",
        "url": "string",
        "caption": "string|null"
      }
    ],
    
    "seo": {
      "metaTitle": "string|null",
      "metaDescription": "string|null",
      "ogImage": "string|null"
    },
    
    "status": "published|draft",
    "publishDate": "date|null",
    "createdAt": "datetime",
    "updatedAt": "datetime"
  }
}
```

### New Fields to Add (Case Studies Table)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `short_description` | VARCHAR(255) | Yes | Brief summary for listing cards |
| `duration` | VARCHAR(100) | No | Project duration (e.g., "4 months") |
| `year` | VARCHAR(4) | No | Project year |
| `challenge_overview` | TEXT | Yes | Challenge paragraph |
| `challenge_points` | JSON | Yes | Array of challenge bullet points |
| `solution_overview` | TEXT | Yes | Solution paragraph |
| `solution_points` | JSON | Yes | Array of solution bullet points |
| `results` | JSON | Yes | Array of {value, label, description} objects |
| `process_steps` | JSON | No | Array of {title, description} objects |
| `technologies` | JSON | Yes | Array of technology name strings |
| `testimonial_quote` | TEXT | No | Client testimonial quote |
| `testimonial_author` | VARCHAR(255) | No | Testimonial author name |
| `testimonial_role` | VARCHAR(255) | No | Testimonial author role/title |
| `gallery` | JSON | No | Array of {type, url, caption} objects |
| `meta_title` | VARCHAR(255) | No | SEO meta title |
| `meta_description` | TEXT | No | SEO meta description |

### Migration SQL

```sql
ALTER TABLE case_studies
ADD COLUMN short_description VARCHAR(255) DEFAULT NULL,
ADD COLUMN duration VARCHAR(100) DEFAULT NULL,
ADD COLUMN year VARCHAR(4) DEFAULT NULL,
ADD COLUMN challenge_overview TEXT DEFAULT NULL,
ADD COLUMN challenge_points JSON DEFAULT NULL,
ADD COLUMN solution_overview TEXT DEFAULT NULL,
ADD COLUMN solution_points JSON DEFAULT NULL,
ADD COLUMN results JSON DEFAULT NULL COMMENT 'Array of {value, label, description}',
ADD COLUMN process_steps JSON DEFAULT NULL,
ADD COLUMN technologies JSON DEFAULT NULL COMMENT 'Array of tech names',
ADD COLUMN testimonial_quote TEXT DEFAULT NULL,
ADD COLUMN testimonial_author VARCHAR(255) DEFAULT NULL,
ADD COLUMN testimonial_role VARCHAR(255) DEFAULT NULL,
ADD COLUMN gallery JSON DEFAULT NULL,
ADD COLUMN meta_title VARCHAR(255) DEFAULT NULL,
ADD COLUMN meta_description TEXT DEFAULT NULL;
```

---

## 3. Blog Posts Endpoint

### Current Response (GET /api/posts/{slug})

The current endpoint appears to return basic post data but is missing some fields the frontend expects.

### Required Response (GET /api/posts - List)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "string",
      "slug": "string",
      "excerpt": "string",
      "featuredImage": "string|null",
      "category": {
        "id": "uuid",
        "name": "string",
        "slug": "string"
      },
      "author": {
        "name": "string",
        "avatar": "string|null"
      },
      "readTime": "string (NEW) - e.g., '5 min read'",
      "publishedAt": "datetime",
      "isFeatured": "boolean (NEW)"
    }
  ],
  "meta": {
    "currentPage": "integer",
    "lastPage": "integer",
    "perPage": "integer",
    "total": "integer"
  }
}
```

### Required Response (GET /api/posts/{slug} - Detail)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "string (required)",
    "slug": "string (required)",
    "excerpt": "string (required)",
    "content": "string (required) - HTML/Markdown content",
    "featuredImage": "string|null",
    
    "category": {
      "id": "uuid",
      "name": "string",
      "slug": "string"
    },
    
    "author": {
      "name": "string (required)",
      "role": "string (NEW) - e.g., 'Lead AI Engineer'",
      "avatar": "string|null",
      "bio": "string|null (NEW)"
    },
    
    "tags": ["string (NEW)"],
    "readTime": "string (NEW) - e.g., '5 min read'",
    
    "seo": {
      "metaTitle": "string|null",
      "metaDescription": "string|null",
      "ogImage": "string|null"
    },
    
    "relatedPosts": [
      {
        "id": "uuid",
        "title": "string",
        "slug": "string",
        "featuredImage": "string|null"
      }
    ],
    
    "commentsEnabled": "boolean",
    "publishedAt": "datetime",
    "createdAt": "datetime",
    "updatedAt": "datetime"
  }
}
```

### New Fields to Add (Posts Table)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `read_time` | VARCHAR(50) | No | Calculated read time string |
| `is_featured` | BOOLEAN | No | Featured post flag |
| `tags` | JSON | No | Array of tag strings |
| `comments_enabled` | BOOLEAN | No | Enable/disable comments |

### New Fields to Add (Admins/Users Table for Authors)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `role_title` | VARCHAR(255) | No | Display role/title (e.g., "Lead AI Engineer") |
| `bio` | TEXT | No | Author biography |

### Migration SQL

```sql
-- Posts table
ALTER TABLE posts
ADD COLUMN read_time VARCHAR(50) DEFAULT NULL,
ADD COLUMN is_featured BOOLEAN DEFAULT FALSE,
ADD COLUMN tags JSON DEFAULT NULL,
ADD COLUMN comments_enabled BOOLEAN DEFAULT TRUE;

-- Admins table (for author info)
ALTER TABLE admins
ADD COLUMN role_title VARCHAR(255) DEFAULT NULL,
ADD COLUMN bio TEXT DEFAULT NULL;
```

---

## 4. Testimonials Endpoint

### Current Response (GET /api/testimonials)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "clientName": "string",
      "company": "string",
      "role": "string",
      "testimonialText": "string",
      "avatar": "string|null",
      "isFeatured": "boolean",
      "status": "published|draft"
    }
  ]
}
```

### Required Response (GET /api/testimonials)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "clientName": "string (required)",
      "company": "string (required)",
      "role": "string (required)",
      "testimonialText": "string (required)",
      "avatar": "string|null",
      "rating": "integer (NEW) - 1-5 star rating",
      "isFeatured": "boolean",
      "projectType": "string|null (NEW) - e.g., 'Web Development'",
      "status": "published|draft",
      "createdAt": "datetime"
    }
  ]
}
```

### New Fields to Add (Testimonials Table)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `rating` | TINYINT | No | Star rating 1-5 (default: 5) |
| `project_type` | VARCHAR(255) | No | Associated service/project type |

### Migration SQL

```sql
ALTER TABLE testimonials
ADD COLUMN rating TINYINT DEFAULT 5,
ADD COLUMN project_type VARCHAR(255) DEFAULT NULL;
```

---

## 5. Brands Endpoint

### Current Response (GET /api/brands)

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "string",
      "logo": "string|null",
      "status": "active|inactive",
      "order": "integer"
    }
  ]
}
```

### Required Response (GET /api/brands)

The current response is adequate. However, ensure `logo` URLs are absolute paths.

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "string (required)",
      "logo": "string|null - Full URL to logo image",
      "websiteUrl": "string|null (NEW) - Company website",
      "status": "active|inactive",
      "order": "integer"
    }
  ]
}
```

### New Fields to Add (Brands Table)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `website_url` | VARCHAR(500) | No | Brand's website URL |

### Migration SQL

```sql
ALTER TABLE brands
ADD COLUMN website_url VARCHAR(500) DEFAULT NULL;
```

---

## 6. Site Settings Endpoint

### Current Response (GET /api/settings)

```json
{
  "success": true,
  "data": {
    "siteName": "string",
    "siteDescription": "string",
    "contactEmail": "string",
    "contactPhone": "string",
    "address": "string",
    "socialLinks": {
      "facebook": "string|null",
      "twitter": "string|null",
      "linkedin": "string|null",
      "instagram": "string|null"
    }
  }
}
```

### Required Response (GET /api/settings)

```json
{
  "success": true,
  "data": {
    "siteName": "string (required)",
    "siteTagline": "string (NEW) - e.g., 'AI-Powered Digital Solutions'",
    "siteDescription": "string (required)",
    
    "logo": {
      "light": "string (NEW) - Logo for dark backgrounds",
      "dark": "string (NEW) - Logo for light backgrounds",
      "favicon": "string (NEW) - Favicon URL"
    },
    
    "contact": {
      "email": "string (required)",
      "phone": "string (required)",
      "whatsapp": "string (NEW) - WhatsApp number with country code",
      "address": "string",
      "city": "string (NEW)",
      "country": "string (NEW)"
    },
    
    "socialLinks": {
      "facebook": "string|null",
      "twitter": "string|null",
      "linkedin": "string|null",
      "instagram": "string|null",
      "youtube": "string|null (NEW)",
      "github": "string|null (NEW)",
      "whatsapp": "string|null (NEW)"
    },
    
    "seo": {
      "defaultTitle": "string (NEW)",
      "titleSeparator": "string (NEW) - e.g., ' | '",
      "defaultDescription": "string (NEW)",
      "defaultKeywords": ["string (NEW)"],
      "ogImage": "string (NEW) - Default OG image"
    },
    
    "footer": {
      "copyrightText": "string (NEW)",
      "showSocialLinks": "boolean (NEW)"
    },
    
    "heroStats": [
      {
        "value": "string (NEW) - e.g., '150+'",
        "label": "string (NEW) - e.g., 'Projects Delivered'"
      }
    ],
    
    "features": {
      "chatbotEnabled": "boolean (NEW)",
      "blogEnabled": "boolean (NEW)",
      "newsletterEnabled": "boolean (NEW)"
    }
  }
}
```

---

## 7. Contact Form Endpoint

### Current Request (POST /api/contact)

```json
{
  "name": "string (required)",
  "email": "string (required)",
  "message": "string (required)",
  "company": "string (optional)",
  "phone": "string (optional)",
  "projectType": "string (optional)",
  "budget": "number (optional)",
  "services": ["string (optional)"]
}
```

### Required Request (POST /api/contact)

The current request format is adequate. Add these optional fields for tracking:

```json
{
  "name": "string (required)",
  "email": "string (required)",
  "message": "string (required)",
  "company": "string (optional)",
  "phone": "string (optional)",
  "projectType": "string (optional) - 'new'|'redesign'|'maintenance'|'consulting'",
  "budget": "string (optional) - e.g., '$25,000'",
  "services": ["string (optional)"],
  
  "metadata": {
    "source": "string (NEW) - Page URL where form was submitted",
    "referrer": "string (NEW) - HTTP referrer",
    "utmSource": "string (NEW)",
    "utmMedium": "string (NEW)",
    "utmCampaign": "string (NEW)"
  }
}
```

### New Fields to Add (Contacts Table)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `source_url` | VARCHAR(500) | No | Page where form was submitted |
| `referrer` | VARCHAR(500) | No | HTTP referrer |
| `utm_source` | VARCHAR(255) | No | UTM source |
| `utm_medium` | VARCHAR(255) | No | UTM medium |
| `utm_campaign` | VARCHAR(255) | No | UTM campaign |
| `ip_address` | VARCHAR(45) | No | Visitor IP (captured server-side) |

### Migration SQL

```sql
ALTER TABLE contacts
ADD COLUMN source_url VARCHAR(500) DEFAULT NULL,
ADD COLUMN referrer VARCHAR(500) DEFAULT NULL,
ADD COLUMN utm_source VARCHAR(255) DEFAULT NULL,
ADD COLUMN utm_medium VARCHAR(255) DEFAULT NULL,
ADD COLUMN utm_campaign VARCHAR(255) DEFAULT NULL,
ADD COLUMN ip_address VARCHAR(45) DEFAULT NULL;
```

---

## 8. AI Chatbot Configuration

### Current State

The chatbot currently uses a hardcoded `SYSTEM_PROMPT` in the frontend and calls OpenRouter API directly from the client.

### Security Issue 🔴

**API keys are exposed in frontend code!** The OpenRouter API key is stored in `VITE_OPENROUTER_API_KEY` environment variable and sent directly from the browser.

### Required Backend Endpoint (NEW)

Create a server-side proxy for AI chat to:
1. Keep API keys secure on the server
2. Allow dynamic configuration from admin panel
3. Rate limit requests per user/IP
4. Log usage for analytics

#### POST /api/chat

**Request:**
```json
{
  "message": "string (required)",
  "conversationId": "string|null - For conversation continuity",
  "context": {
    "page": "string - Current page URL",
    "previousMessages": [
      {
        "role": "user|assistant",
        "content": "string"
      }
    ]
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "string - AI response",
    "conversationId": "string"
  }
}
```

#### GET /api/chatbot/config (Public)

Returns public chatbot configuration:

```json
{
  "success": true,
  "data": {
    "isEnabled": "boolean",
    "greetingMessage": "string",
    "suggestedQuestions": ["string"],
    "companyInfo": {
      "name": "string",
      "services": ["string"],
      "contactEmail": "string",
      "contactPhone": "string"
    }
  }
}
```

### New Database Table: chatbot_conversations

```sql
CREATE TABLE chatbot_conversations (
  id CHAR(36) PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  user_id CHAR(36) DEFAULT NULL,
  ip_address VARCHAR(45),
  messages JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_session (session_id),
  INDEX idx_created (created_at)
);
```

### Environment Variables (Server-Side Only)

```env
# NEVER expose these to frontend
OPENROUTER_API_KEY=sk-or-v1-xxxxx
OPENROUTER_MODEL=qwen/qwen3-4b:free
CHATBOT_MAX_TOKENS=500
CHATBOT_TEMPERATURE=0.7
CHATBOT_RATE_LIMIT=10  # requests per minute per IP
```

---

## 9. New Endpoints Required

### 9.1 Newsletter Subscription

#### POST /api/newsletter/subscribe

```json
// Request
{
  "email": "string (required)",
  "consent": "boolean (required) - GDPR consent"
}

// Response
{
  "success": true,
  "message": "Successfully subscribed to newsletter"
}
```

### 9.2 Service Inquiry Form

#### POST /api/inquiries

```json
// Request
{
  "serviceSlug": "string (required)",
  "name": "string (required)",
  "email": "string (required)",
  "phone": "string (optional)",
  "company": "string (optional)",
  "budgetRange": "string (optional) - '$5k-$10k'|'$10k-$25k'|'$25k-$50k'|'$50k+'",
  "timeline": "string (optional) - 'urgent'|'1-3months'|'3-6months'|'flexible'",
  "message": "string (required)"
}

// Response
{
  "success": true,
  "message": "Inquiry submitted successfully"
}
```

### 9.3 Related Content Endpoints

#### GET /api/posts/{slug}/related

Returns 3 related blog posts based on category and tags.

```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "string",
      "slug": "string",
      "excerpt": "string",
      "featuredImage": "string|null",
      "publishedAt": "datetime"
    }
  ]
}
```

#### GET /api/case-studies/{slug}/related

Returns related case studies based on industry.

---

## 10. Environment Variables

### Required Server-Side Variables

```env
# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=smatatech
DB_USERNAME=root
DB_PASSWORD=

# API Keys (NEVER expose to frontend)
OPENROUTER_API_KEY=sk-or-v1-xxxxx
BREVO_API_KEY=xkeysib-xxxxx

# Email
MAIL_FROM_ADDRESS=info@smatatech.com.ng
MAIL_FROM_NAME=Smatatech

# Security
JWT_SECRET=xxxxx
JWT_TTL=1440

# URLs
APP_URL=https://api.smatatech.com.ng
FRONTEND_URL=https://smatatech.com.ng
ADMIN_FRONTEND_URL=https://admin.smatatech.com.ng
```

### Frontend Environment Variables (Safe to Expose)

```env
VITE_API_BASE_URL=https://api.smatatech.com.ng/api
VITE_ADMIN_API_BASE_URL=https://api.smatatech.com.ng/api
VITE_PUBLIC_API_BASE_URL=https://api.smatatech.com.ng/api
VITE_USE_MOCK_API=false

# Remove this - API calls should go through backend proxy
# VITE_OPENROUTER_API_KEY=  <- REMOVE THIS
```

---

## Summary of Required Changes

### Database Migrations Needed

1. **services** - Add `features`, `benefits`, `process_steps`, SEO fields
2. **case_studies** - Add structured challenge/solution, `results`, `technologies`, `testimonial`, `gallery`
3. **posts** - Add `read_time`, `is_featured`, `tags`, `comments_enabled`
4. **testimonials** - Add `rating`, `project_type`
5. **brands** - Add `website_url`
6. **contacts** - Add tracking metadata fields
7. **admins** - Add `role_title`, `bio` for author display
8. **site_settings** - Add extensive new settings keys
9. **chatbot_conversations** - New table for chat logging

### New Endpoints Needed

1. `POST /api/chat` - Server-side AI chat proxy
2. `GET /api/chatbot/config` - Public chatbot config
3. `POST /api/newsletter/subscribe` - Newsletter signup
4. `POST /api/inquiries` - Service inquiry form
5. `GET /api/posts/{slug}/related` - Related posts
6. `GET /api/case-studies/{slug}/related` - Related case studies

### Security Fixes Needed

1. Move OpenRouter API key to server-side
2. Create chat proxy endpoint
3. Add rate limiting for chat endpoint
4. Add CSRF protection for all form submissions

---

## Implementation Priority

| Priority | Task | Effort |
|----------|------|--------|
| 1 | Add service detail fields (features, benefits, process) | Medium |
| 2 | Add case study structured fields | High |
| 3 | Create chat proxy endpoint | Medium |
| 4 | Add blog post author role and tags | Low |
| 5 | Expand site settings | Medium |
| 6 | Add testimonial rating | Low |
| 7 | Add contact tracking metadata | Low |
| 8 | Create newsletter endpoint | Low |

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-21  
**Author:** Frontend Systems Auditor AI
