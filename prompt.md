# Frontend Integration Prompt - Smatatech Website

## CRITICAL MISSION

You are a Senior Frontend Developer. Your task is to **completely integrate** this website with the live production backend API.

**Backend API Base URL:** `https://api.smatatech.com.ng/api`

**Primary Objectives:**
1. **MIGRATE existing mock/demo data to the backend database FIRST**
2. Replace frontend mock data with real API calls
3. Implement proper authentication flows
4. Handle loading states, errors, and edge cases
5. Ensure production-ready code quality

> ⚠️ **IMPORTANT:** Do NOT simply delete mock data! The existing demo content (services, case studies, testimonials, blog posts, etc.) should be migrated to the backend database to serve as initial content for the live website.

---

## SECTION 0: DATA MIGRATION STRATEGY (DO THIS FIRST!)

### Overview

Before removing any mock/demo data from the frontend, you MUST migrate it to the backend database via the Admin API. This ensures the website has real content after integration.



Create a one-time migration script that runs all migrations:

```javascript
// scripts/migrateDataToBackend.js

const API_BASE = 'https://api.smatatech.com.ng/api';

// Import all your mock data
import { mockServices } from '../src/data/services';
import { mockPosts } from '../src/data/posts';
import { mockCategories } from '../src/data/categories';
import { mockCaseStudies } from '../src/data/caseStudies';
import { mockTestimonials } from '../src/data/testimonials';
import { mockBrands } from '../src/data/brands';
import { siteSettings } from '../src/data/settings';

async function migrateAllData() {
  console.log('🚀 Starting data migration to backend...\n');
  
  // 1. Login
  console.log('📝 Logging in as admin...');
  const loginRes = await fetch(`${API_BASE}/admin/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ email: 'admin@smatatech.com', password: 'password' })
  });
  const { data: authData } = await loginRes.json();
  const token = authData.token;
  console.log('✅ Logged in successfully\n');

  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`,
  };

  // 2. Migrate Services
  console.log('📦 Migrating services...');
  for (const service of mockServices) {
    try {
      await fetch(`${API_BASE}/admin/services`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          title: service.title,
          slug: service.slug || service.title.toLowerCase().replace(/\s+/g, '-'),
          shortDescription: service.shortDescription || service.description?.substring(0, 150),
          description: service.description,
          icon: service.icon,
          image: service.image,
          features: service.features || [],
          isActive: true,
        })
      });
      console.log(`  ✅ ${service.title}`);
    } catch (e) {
      console.log(`  ❌ ${service.title}: ${e.message}`);
    }
  }

  // 3. Migrate Categories
  console.log('\n📂 Migrating categories...');
  const categoryMap = {};
  for (const category of mockCategories) {
    try {
      const res = await fetch(`${API_BASE}/admin/categories`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          name: category.name,
          slug: category.slug || category.name.toLowerCase().replace(/\s+/g, '-'),
          description: category.description,
          isActive: true,
        })
      });
      const { data } = await res.json();
      categoryMap[category.id || category.name] = data.id;
      console.log(`  ✅ ${category.name}`);
    } catch (e) {
      console.log(`  ❌ ${category.name}: ${e.message}`);
    }
  }

  // 4. Migrate Blog Posts
  console.log('\n📝 Migrating blog posts...');
  for (const post of mockPosts) {
    try {
      await fetch(`${API_BASE}/admin/posts`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          title: post.title,
          slug: post.slug || post.title.toLowerCase().replace(/\s+/g, '-'),
          content: post.content,
          excerpt: post.excerpt,
          categoryId: categoryMap[post.category] || categoryMap[post.categoryId] || null,
          featuredImage: post.featuredImage || post.image,
          status: 'published',
          tags: post.tags || [],
          metaTitle: post.metaTitle || post.title,
          metaDescription: post.metaDescription || post.excerpt,
        })
      });
      console.log(`  ✅ ${post.title}`);
    } catch (e) {
      console.log(`  ❌ ${post.title}: ${e.message}`);
    }
  }

  // 5. Migrate Case Studies
  console.log('\n💼 Migrating case studies...');
  for (const study of mockCaseStudies) {
    try {
      await fetch(`${API_BASE}/admin/case-studies`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          title: study.title,
          slug: study.slug || study.title.toLowerCase().replace(/\s+/g, '-'),
          client: study.client,
          industry: study.industry,
          shortDescription: study.shortDescription || study.description?.substring(0, 200),
          description: study.description,
          challenge: study.challenge,
          solution: study.solution,
          results: study.results,
          technologies: study.technologies || [],
          featuredImage: study.featuredImage || study.image,
          gallery: study.gallery || [],
          projectUrl: study.projectUrl,
          isPublished: true,
        })
      });
      console.log(`  ✅ ${study.title}`);
    } catch (e) {
      console.log(`  ❌ ${study.title}: ${e.message}`);
    }
  }

  // 6. Migrate Testimonials
  console.log('\n⭐ Migrating testimonials...');
  for (const testimonial of mockTestimonials) {
    try {
      await fetch(`${API_BASE}/admin/testimonials`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          name: testimonial.name,
          position: testimonial.position || testimonial.role,
          company: testimonial.company,
          content: testimonial.content || testimonial.text || testimonial.quote,
          avatar: testimonial.avatar || testimonial.image,
          rating: testimonial.rating || 5,
          isFeatured: true,
          isActive: true,
        })
      });
      console.log(`  ✅ ${testimonial.name}`);
    } catch (e) {
      console.log(`  ❌ ${testimonial.name}: ${e.message}`);
    }
  }

  // 7. Migrate Brands
  console.log('\n🏢 Migrating brands...');
  for (const brand of mockBrands) {
    try {
      await fetch(`${API_BASE}/admin/brands`, {
        method: 'POST',
        headers,
        body: JSON.stringify({
          name: brand.name,
          logo: brand.logo || brand.image,
          website: brand.website || brand.url,
          isActive: true,
        })
      });
      console.log(`  ✅ ${brand.name}`);
    } catch (e) {
      console.log(`  ❌ ${brand.name}: ${e.message}`);
    }
  }

  // 8. Update Site Settings
  console.log('\n⚙️ Updating site settings...');
  try {
    await fetch(`${API_BASE}/admin/settings`, {
      method: 'PUT',
      headers,
      body: JSON.stringify(siteSettings)
    });
    console.log('  ✅ Site settings updated');
  } catch (e) {
    console.log(`  ❌ Settings: ${e.message}`);
  }

  console.log('\n🎉 Migration complete!');
  console.log('\n⚠️  NEXT STEPS:');
  console.log('1. Verify data in admin dashboard');
  console.log('2. Upload any local images to backend via admin panel');
  console.log('3. Update image URLs in migrated content if needed');
  console.log('4. THEN remove mock data files from frontend');
}

// Run migration
migrateAllData().catch(console.error);
```

### Step 5: Handle Image Migration

For images referenced in mock data:

**Option A: Keep External URLs**
If mock data uses external image URLs (Unsplash, placeholder services), they'll continue to work.

**Option B: Upload to Backend**
For local images, upload them via the admin API:

```javascript
async function uploadImage(filePath, token) {
  const formData = new FormData();
  formData.append('file', fs.createReadStream(filePath));
  formData.append('folder', 'content');
  
  const response = await fetch('https://api.smatatech.com.ng/api/admin/upload', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
    body: formData
  });
  
  const { data } = await response.json();
  return data.url; // Use this URL in your content
}
```

**Option C: Use Placeholder Service Temporarily**
Replace local images with placeholder URLs temporarily:
```javascript
const placeholderImage = (width, height) => 
  `https://via.placeholder.com/${width}x${height}`;
```

---

### Step 6: Verify Migration Success

After running the migration script:

1. **Check via API:**
```javascript
// Verify services
const services = await fetch('https://api.smatatech.com.ng/api/services').then(r => r.json());
console.log(`Services migrated: ${services.data.length}`);

// Verify posts
const posts = await fetch('https://api.smatatech.com.ng/api/posts').then(r => r.json());
console.log(`Posts migrated: ${posts.data.length}`);

// Continue for other content types...
```

2. **Check via Admin Dashboard:**
   - Login at your admin dashboard URL
   - Verify each content section has data

3. **Check Public Endpoints:**
   - Visit `https://api.smatatech.com.ng/api/services`
   - Visit `https://api.smatatech.com.ng/api/posts`
   - Visit `https://api.smatatech.com.ng/api/testimonials`
   - etc.

---

### Step 7: ONLY THEN Remove Mock Data

After confirming all data is in the database:

1. Remove mock data files
2. Remove mock data imports
3. Replace with API calls

```javascript
// BEFORE (mock data)
import { services } from './data/services';

function ServicesPage() {
  return services.map(s => <ServiceCard service={s} />);
}

// AFTER (API data)
function ServicesPage() {
  const [services, setServices] = useState([]);
  
  useEffect(() => {
    fetch('https://api.smatatech.com.ng/api/services')
      .then(r => r.json())
      .then(data => setServices(data.data));
  }, []);
  
  return services.map(s => <ServiceCard service={s} />);
}
```

---

## BACKEND API OVERVIEW

The backend is a Laravel API with the following structure:
- **Public endpoints** - No authentication required
- **User endpoints** - Require user authentication (Bearer token)
- **Admin endpoints** - Require admin authentication (Bearer token)

**Authentication Method:** Laravel Sanctum (Bearer Token)
- All authenticated requests must include: `Authorization: Bearer {token}`
- All requests must include: `Accept: application/json`
- POST/PUT requests must include: `Content-Type: application/json`

---

## SECTION 1: API CLIENT SETUP

### Required Configuration

Create or update your API client with these settings:

```javascript
// api/config.js or similar
const API_BASE_URL = 'https://api.smatatech.com.ng/api';

const apiClient = {
  baseURL: API_BASE_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  // Add token to requests when available
  getAuthHeaders: (token) => ({
    'Authorization': `Bearer ${token}`,
  }),
};
```

### Response Format

All API responses follow this structure:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Paginated Response:**
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

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": { "field": ["Error message"] }
}
```

---

## SECTION 2: PUBLIC WEBSITE ENDPOINTS

These endpoints power the public-facing website. **No authentication required.**

### 2.1 Health Check
```
GET /health
```
Use for: Connection testing, monitoring
```javascript
// Example
const checkHealth = async () => {
  const response = await fetch(`${API_BASE_URL}/health`);
  return response.json();
};
```

### 2.2 Site Settings
```
GET /settings
```
Use for: Site name, logo, contact info, social links, SEO defaults
```javascript
// Response structure
{
  "success": true,
  "data": {
    "siteName": "Smatatech",
    "siteDescription": "...",
    "contactEmail": "contact@smatatech.com.ng",
    "contactPhone": "+234...",
    "address": "...",
    "socialLinks": {
      "facebook": "...",
      "twitter": "...",
      "linkedin": "...",
      "instagram": "..."
    },
    "logo": "https://...",
    "favicon": "https://..."
  }
}
```

### 2.3 Services
```
GET /services              - List all services
GET /services/{slug}       - Get single service by slug
```
Use for: Services page, homepage services section
```javascript
// Response structure for list
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Web Development",
      "slug": "web-development",
      "shortDescription": "Custom web solutions",
      "description": "Full HTML description...",
      "icon": "code",
      "image": "https://...",
      "features": ["Feature 1", "Feature 2"],
      "order": 1,
      "isActive": true
    }
  ]
}
```

### 2.4 Case Studies / Portfolio
```
GET /case-studies              - List all (paginated)
GET /case-studies/{slug}       - Get single by slug
```
Query params: `?per_page=12`
```javascript
// Response structure
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Project Name",
      "slug": "project-name",
      "client": "Client Name",
      "industry": "Technology",
      "shortDescription": "Brief overview",
      "description": "Full description...",
      "challenge": "The challenge...",
      "solution": "Our solution...",
      "results": "The results...",
      "featuredImage": "https://...",
      "gallery": ["https://...", "https://..."],
      "technologies": ["React", "Laravel"],
      "projectUrl": "https://...",
      "completedAt": "2024-01-15"
    }
  ],
  "meta": { ... }
}
```

### 2.5 Testimonials
```
GET /testimonials
```
Query params: `?featured=true` (optional)
```javascript
// Response structure
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "John Doe",
      "position": "CEO",
      "company": "Tech Corp",
      "content": "Testimonial text...",
      "avatar": "https://...",
      "rating": 5,
      "isFeatured": true
    }
  ]
}
```

### 2.6 Blog Posts
```
GET /posts                 - List all (paginated)
GET /posts/{slug}          - Get single by slug
```
Query params: `?per_page=10&category=tech&search=keyword`
```javascript
// Response structure for list
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Blog Post Title",
      "slug": "blog-post-title",
      "excerpt": "Short excerpt...",
      "content": "Full HTML content...",
      "featuredImage": "https://...",
      "category": {
        "id": "uuid",
        "name": "Technology",
        "slug": "technology"
      },
      "author": {
        "name": "Author Name",
        "avatar": "https://..."
      },
      "tags": ["tag1", "tag2"],
      "readTime": "5 min read",
      "publishedAt": "2024-01-20T10:00:00+00:00",
      "metaTitle": "SEO Title",
      "metaDescription": "SEO Description"
    }
  ],
  "meta": { ... }
}
```

### 2.7 Blog Categories
```
GET /categories
```
```javascript
// Response structure
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Technology",
      "slug": "technology",
      "description": "Tech articles",
      "postCount": 15
    }
  ]
}
```

### 2.8 Brand Logos / Clients
```
GET /brands
```
Use for: "Trusted by" section, client logos
```javascript
// Response structure
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Company Name",
      "logo": "https://...",
      "website": "https://...",
      "order": 1
    }
  ]
}
```

### 2.9 Contact Form Submission
```
POST /contact
```
**Rate Limited: 3 requests per minute**
```javascript
// Request body
{
  "name": "John Doe",           // required
  "email": "john@example.com",  // required, valid email
  "company": "Company Inc",     // optional
  "phone": "+1234567890",       // optional
  "projectType": "website",     // optional
  "budget": "5000-10000",       // optional
  "services": ["web-development", "design"], // optional array
  "message": "I need help with..." // required
}

// Success response (201)
{
  "success": true,
  "message": "Thank you for your message. We will get back to you soon."
}

// Validation error (422)
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

## SECTION 3: USER AUTHENTICATION

Implement these for public user registration and login (for AI tools access).

### 3.1 User Registration
```
POST /auth/register
```
```javascript
// Request body
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}

// Success response (201)
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "credits": 50,
      "status": "active"
    },
    "token": "1|abc123xxx...",
    "expiresAt": "2024-01-27T12:00:00+00:00"
  }
}
```

### 3.2 User Login
```
POST /auth/login
```
```javascript
// Request body
{
  "email": "john@example.com",
  "password": "password123"
}

// Success response (200)
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "1|abc123xxx...",
    "expiresAt": "2024-01-27T12:00:00+00:00"
  }
}

// Error response (422)
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### 3.3 User Logout
```
POST /auth/logout
```
**Requires:** `Authorization: Bearer {token}`
```javascript
// Success response (200)
{
  "success": true,
  "message": "Successfully logged out."
}
```

### 3.4 Get Current User
```
GET /auth/me
```
**Requires:** `Authorization: Bearer {token}`

### 3.5 Refresh Token
```
POST /auth/refresh
```
**Requires:** `Authorization: Bearer {token}`

### 3.6 Forgot Password
```
POST /auth/forgot-password
```
```javascript
// Request body
{
  "email": "john@example.com"
}

// Success response
{
  "success": true,
  "message": "Password reset link sent to your email."
}
```

### 3.7 Reset Password
```
POST /auth/reset-password
```
```javascript
// Request body
{
  "token": "reset_token_from_email",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## SECTION 4: ADMIN AUTHENTICATION

For the admin dashboard application.

### 4.1 Admin Login
```
POST /admin/login
```
```javascript
// Request body
{
  "email": "admin@smatatech.com",
  "password": "password"
}

// Success response (200)
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "name": "Admin",
      "email": "admin@smatatech.com",
      "role": "super_admin"
    },
    "token": "2|admintoken...",
    "expiresAt": "2024-01-21T12:00:00+00:00"
  }
}
```

### 4.2 Admin Logout
```
POST /admin/logout
```
**Requires:** `Authorization: Bearer {admin_token}`

### 4.3 Get Current Admin
```
GET /admin/me
```
**Requires:** `Authorization: Bearer {admin_token}`

### 4.4 Refresh Admin Token
```
POST /admin/refresh
```
**Requires:** `Authorization: Bearer {admin_token}`

---

## SECTION 5: ADMIN DASHBOARD ENDPOINTS

All require: `Authorization: Bearer {admin_token}`

### 5.1 Dashboard Statistics
```
GET /admin/dashboard/stats
```
```javascript
// Response
{
  "success": true,
  "data": {
    "totalUsers": 150,
    "totalPosts": 45,
    "totalContacts": 23,
    "totalServices": 8,
    "recentContacts": 5,
    "publishedPosts": 40
  }
}
```

### 5.2 Activity Log
```
GET /admin/dashboard/activity
```

### 5.3 User Management
```
GET    /admin/users              - List users (paginated)
GET    /admin/users/{id}         - Get user details
POST   /admin/users              - Create user
PUT    /admin/users/{id}         - Update user
DELETE /admin/users/{id}         - Delete user (super_admin only)
POST   /admin/users/{id}/activate    - Activate user
POST   /admin/users/{id}/deactivate  - Deactivate user
```

### 5.4 Blog Posts Management
```
GET    /admin/posts              - List all posts
GET    /admin/posts/{id}         - Get post details
POST   /admin/posts              - Create post
PUT    /admin/posts/{id}         - Update post
DELETE /admin/posts/{id}         - Delete post
POST   /admin/posts/{id}/publish   - Publish post
POST   /admin/posts/{id}/unpublish - Unpublish post
```

**Create/Update Post Request:**
```javascript
{
  "title": "Post Title",
  "slug": "post-title",           // auto-generated if omitted
  "content": "<p>HTML content</p>",
  "excerpt": "Short excerpt",
  "categoryId": "uuid",
  "featuredImage": "https://...",
  "status": "draft",              // draft, published
  "tags": ["tag1", "tag2"],
  "metaTitle": "SEO Title",
  "metaDescription": "SEO Description"
}
```

### 5.5 Categories Management
```
GET    /admin/categories         - List all
GET    /admin/categories/{id}    - Get details
POST   /admin/categories         - Create
PUT    /admin/categories/{id}    - Update
DELETE /admin/categories/{id}    - Delete
```

### 5.6 Services Management
```
GET    /admin/services           - List all
GET    /admin/services/{id}      - Get details
POST   /admin/services           - Create
PUT    /admin/services/{id}      - Update
DELETE /admin/services/{id}      - Delete
POST   /admin/services/reorder   - Reorder services
```

**Reorder Request:**
```javascript
{
  "order": ["uuid1", "uuid2", "uuid3"]
}
```

### 5.7 Case Studies Management
```
GET    /admin/case-studies       - List all
GET    /admin/case-studies/{id}  - Get details
POST   /admin/case-studies       - Create
PUT    /admin/case-studies/{id}  - Update
DELETE /admin/case-studies/{id}  - Delete
```

### 5.8 Testimonials Management
```
GET    /admin/testimonials       - List all
GET    /admin/testimonials/{id}  - Get details
POST   /admin/testimonials       - Create
PUT    /admin/testimonials/{id}  - Update
DELETE /admin/testimonials/{id}  - Delete
```

### 5.9 Brands Management
```
GET    /admin/brands             - List all
GET    /admin/brands/{id}        - Get details
POST   /admin/brands             - Create
PUT    /admin/brands/{id}        - Update
DELETE /admin/brands/{id}        - Delete
POST   /admin/brands/reorder     - Reorder brands
```

### 5.10 Comments Management
```
GET    /admin/comments           - List all
GET    /admin/comments/{id}      - Get details
POST   /admin/comments/{id}/approve  - Approve
POST   /admin/comments/{id}/reject   - Reject
DELETE /admin/comments/{id}      - Delete
```

### 5.11 Contact Submissions
```
GET    /admin/contacts           - List all (paginated)
GET    /admin/contacts/{id}      - Get details
POST   /admin/contacts/{id}/read     - Mark as read
POST   /admin/contacts/{id}/unread   - Mark as unread
DELETE /admin/contacts/{id}      - Delete
```

### 5.12 Site Settings
```
GET /admin/settings              - Get all settings
PUT /admin/settings              - Update settings
```

### 5.13 File Upload
```
POST   /admin/upload             - Upload file
DELETE /admin/upload             - Delete file
```

**Upload Request:** `multipart/form-data`
```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('folder', 'posts'); // optional

// Response
{
  "success": true,
  "data": {
    "url": "https://api.smatatech.com.ng/storage/uploads/filename.jpg",
    "path": "uploads/filename.jpg"
  }
}
```

**Delete Request:**
```javascript
{
  "path": "uploads/filename.jpg"
}
```

### 5.14 Email Settings
```
GET /admin/email/settings        - Get email settings
PUT /admin/email/settings        - Update email settings
GET /admin/email/templates       - List templates
GET /admin/email/templates/{id}  - Get template
PUT /admin/email/templates/{id}  - Update template
```

### 5.15 Chatbot Configuration
```
GET  /admin/chatbot/config       - Get config
PUT  /admin/chatbot/config       - Update config
POST /admin/chatbot/toggle       - Toggle on/off
```

---

## SECTION 6: AI TOOLS ENDPOINTS (User Auth Required)

For authenticated users to access AI tools.

### 6.1 List AI Tools
```
GET /ai/tools
```
**Requires:** `Authorization: Bearer {user_token}`

### 6.2 Get Credit Balance
```
GET /ai/credits
```
```javascript
// Response
{
  "success": true,
  "data": {
    "available": 50,
    "used": 10,
    "total": 60
  }
}
```

### 6.3 Get Usage History
```
GET /ai/usage
```
Query params: `?per_page=15`

### 6.4 Execute AI Tool
```
POST /ai/tools/{id}/execute
```
```javascript
// Request
{
  "input": "Your input text",
  "options": {}
}

// Success response
{
  "success": true,
  "data": {
    "output": "AI generated output...",
    "creditsUsed": 5,
    "remainingCredits": 45
  }
}

// Insufficient credits (402)
{
  "success": false,
  "message": "Insufficient credits...",
  "errors": {
    "required": 5,
    "available": 2
  }
}
```

---

## SECTION 6B: AI TOOLS PAGE (NEW FEATURE - REQUIRED)

### Overview

Create a new **Tools** page that will serve as the gateway to AI-powered tools. This page must be added to the main navigation and handle user authentication.

### Requirements

1. **Add to Navigation Menu**
   - Add "Tools" link to the main navigation bar
   - Position it prominently (e.g., after Services or before Contact)
   - Make it visually distinct (optional: add icon, badge, or highlight)

2. **Page URL:** `/tools`

3. **Two States:**
   - **Unauthenticated:** Show login/register forms
   - **Authenticated:** Show "Coming Soon" page for AI tools

---

### Navigation Menu Update

```jsx
// Example: Add to your navigation component
const navLinks = [
  { name: 'Home', href: '/' },
  { name: 'Services', href: '/services' },
  { name: 'Portfolio', href: '/portfolio' },
  { name: 'Blog', href: '/blog' },
  { name: 'Tools', href: '/tools' },  // ← ADD THIS
  { name: 'Contact', href: '/contact' },
];

// Optional: Style it differently
<NavLink 
  to="/tools" 
  className="nav-link tools-link"
>
  <SparklesIcon className="w-4 h-4" /> {/* Optional icon */}
  Tools
  <span className="badge">New</span> {/* Optional badge */}
</NavLink>
```

---

### Tools Page - Unauthenticated State

When user is NOT logged in, show a welcoming page with login/register options:

```jsx
// pages/Tools.jsx or similar

import { useState } from 'react';
import { useAuth } from '../hooks/useAuth'; // Your auth hook/context

function ToolsPage() {
  const { user, isAuthenticated } = useAuth();
  
  if (isAuthenticated) {
    return <ToolsComingSoon user={user} />;
  }
  
  return <ToolsAuthGateway />;
}

function ToolsAuthGateway() {
  const [activeTab, setActiveTab] = useState('login'); // 'login' or 'register'
  
  return (
    <div className="tools-page">
      {/* Hero Section */}
      <section className="tools-hero">
        <div className="container">
          <h1>AI-Powered Tools</h1>
          <p className="subtitle">
            Access our suite of intelligent tools designed to boost your productivity.
            Login or create an account to get started.
          </p>
          
          {/* Feature Preview */}
          <div className="tools-preview">
            <div className="tool-preview-card">
              <span className="icon">✍️</span>
              <h3>Content Writer</h3>
              <p>Generate high-quality content with AI</p>
            </div>
            <div className="tool-preview-card">
              <span className="icon">🎨</span>
              <h3>Image Generator</h3>
              <p>Create stunning visuals instantly</p>
            </div>
            <div className="tool-preview-card">
              <span className="icon">💻</span>
              <h3>Code Assistant</h3>
              <p>Get help with coding tasks</p>
            </div>
            <div className="tool-preview-card">
              <span className="icon">📊</span>
              <h3>Data Analyzer</h3>
              <p>Extract insights from your data</p>
            </div>
          </div>
        </div>
      </section>
      
      {/* Auth Section */}
      <section className="tools-auth-section">
        <div className="auth-container">
          {/* Tab Switcher */}
          <div className="auth-tabs">
            <button 
              className={`tab ${activeTab === 'login' ? 'active' : ''}`}
              onClick={() => setActiveTab('login')}
            >
              Login
            </button>
            <button 
              className={`tab ${activeTab === 'register' ? 'active' : ''}`}
              onClick={() => setActiveTab('register')}
            >
              Create Account
            </button>
          </div>
          
          {/* Forms */}
          {activeTab === 'login' ? (
            <LoginForm onSwitchToRegister={() => setActiveTab('register')} />
          ) : (
            <RegisterForm onSwitchToLogin={() => setActiveTab('login')} />
          )}
        </div>
      </section>
    </div>
  );
}
```

---

### Login Form Component

```jsx
function LoginForm({ onSwitchToRegister }) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    
    try {
      const response = await fetch('https://api.smatatech.com.ng/api/auth/login', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Login failed');
      }
      
      // Store token and user data
      login(data.data.token, data.data.user);
      
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <form onSubmit={handleSubmit} className="auth-form">
      <h2>Welcome Back</h2>
      <p className="form-subtitle">Login to access AI tools</p>
      
      {error && <div className="error-message">{error}</div>}
      
      <div className="form-group">
        <label htmlFor="email">Email Address</label>
        <input
          type="email"
          id="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="you@example.com"
          required
        />
      </div>
      
      <div className="form-group">
        <label htmlFor="password">Password</label>
        <input
          type="password"
          id="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="••••••••"
          required
        />
      </div>
      
      <button type="submit" className="btn-primary" disabled={loading}>
        {loading ? 'Logging in...' : 'Login'}
      </button>
      
      <div className="form-footer">
        <a href="/forgot-password" className="forgot-link">Forgot password?</a>
        <p>
          Don't have an account?{' '}
          <button type="button" onClick={onSwitchToRegister} className="link-button">
            Create one
          </button>
        </p>
      </div>
    </form>
  );
}
```

---

### Register Form Component

```jsx
function RegisterForm({ onSwitchToLogin }) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [error, setError] = useState('');
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  
  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    setErrors({ ...errors, [e.target.name]: '' });
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setErrors({});
    setLoading(true);
    
    try {
      const response = await fetch('https://api.smatatech.com.ng/api/auth/register', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        if (data.errors) {
          setErrors(data.errors);
        }
        throw new Error(data.message || 'Registration failed');
      }
      
      // Auto-login after registration
      login(data.data.token, data.data.user);
      
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <form onSubmit={handleSubmit} className="auth-form">
      <h2>Create Account</h2>
      <p className="form-subtitle">Join us to access AI-powered tools</p>
      
      {error && <div className="error-message">{error}</div>}
      
      <div className="form-group">
        <label htmlFor="name">Full Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value={formData.name}
          onChange={handleChange}
          placeholder="John Doe"
          required
        />
        {errors.name && <span className="field-error">{errors.name[0]}</span>}
      </div>
      
      <div className="form-group">
        <label htmlFor="reg-email">Email Address</label>
        <input
          type="email"
          id="reg-email"
          name="email"
          value={formData.email}
          onChange={handleChange}
          placeholder="you@example.com"
          required
        />
        {errors.email && <span className="field-error">{errors.email[0]}</span>}
      </div>
      
      <div className="form-group">
        <label htmlFor="reg-password">Password</label>
        <input
          type="password"
          id="reg-password"
          name="password"
          value={formData.password}
          onChange={handleChange}
          placeholder="Min. 8 characters"
          required
          minLength={8}
        />
        {errors.password && <span className="field-error">{errors.password[0]}</span>}
      </div>
      
      <div className="form-group">
        <label htmlFor="password_confirmation">Confirm Password</label>
        <input
          type="password"
          id="password_confirmation"
          name="password_confirmation"
          value={formData.password_confirmation}
          onChange={handleChange}
          placeholder="Repeat password"
          required
        />
      </div>
      
      <button type="submit" className="btn-primary" disabled={loading}>
        {loading ? 'Creating Account...' : 'Create Account'}
      </button>
      
      <div className="form-footer">
        <p>
          Already have an account?{' '}
          <button type="button" onClick={onSwitchToLogin} className="link-button">
            Login
          </button>
        </p>
      </div>
    </form>
  );
}
```

---

### Tools Page - Authenticated State (Coming Soon)

```jsx
function ToolsComingSoon({ user }) {
  const { logout } = useAuth();
  
  const handleLogout = async () => {
    try {
      await fetch('https://api.smatatech.com.ng/api/auth/logout', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      });
    } catch (e) {
      // Ignore errors, logout locally anyway
    }
    logout();
  };
  
  return (
    <div className="tools-coming-soon">
      {/* User Header */}
      <div className="user-header">
        <div className="container">
          <div className="user-info">
            <span>Welcome, <strong>{user.name}</strong></span>
            <span className="credits-badge">
              💳 {user.credits || 0} Credits
            </span>
          </div>
          <button onClick={handleLogout} className="btn-logout">
            Logout
          </button>
        </div>
      </div>
      
      {/* Coming Soon Content */}
      <section className="coming-soon-hero">
        <div className="container">
          <div className="coming-soon-content">
            <div className="icon-large">🚀</div>
            <h1>AI Tools Coming Soon!</h1>
            <p className="lead">
              We're building something amazing. Our suite of AI-powered tools 
              will help you create, analyze, and automate like never before.
            </p>
            
            {/* Launch Timeline */}
            <div className="launch-info">
              <div className="launch-badge">
                <span className="label">Expected Launch</span>
                <span className="date">Q2 2024</span>
              </div>
            </div>
            
            {/* Upcoming Tools Preview */}
            <div className="upcoming-tools">
              <h2>What's Coming</h2>
              <div className="tools-grid">
                <div className="tool-card">
                  <div className="tool-icon">✍️</div>
                  <h3>AI Content Writer</h3>
                  <p>Generate blog posts, marketing copy, and more with advanced AI</p>
                  <span className="tool-credits">~5 credits/use</span>
                </div>
                
                <div className="tool-card">
                  <div className="tool-icon">🎨</div>
                  <h3>Image Generator</h3>
                  <p>Create stunning images from text descriptions</p>
                  <span className="tool-credits">~10 credits/use</span>
                </div>
                
                <div className="tool-card">
                  <div className="tool-icon">💻</div>
                  <h3>Code Assistant</h3>
                  <p>Get help debugging, writing, and explaining code</p>
                  <span className="tool-credits">~3 credits/use</span>
                </div>
                
                <div className="tool-card">
                  <div className="tool-icon">📝</div>
                  <h3>Document Summarizer</h3>
                  <p>Quickly summarize long documents and articles</p>
                  <span className="tool-credits">~2 credits/use</span>
                </div>
                
                <div className="tool-card">
                  <div className="tool-icon">🌐</div>
                  <h3>Translation Tool</h3>
                  <p>Translate content between 50+ languages</p>
                  <span className="tool-credits">~2 credits/use</span>
                </div>
                
                <div className="tool-card">
                  <div className="tool-icon">📊</div>
                  <h3>Data Analyzer</h3>
                  <p>Extract insights and visualize your data</p>
                  <span className="tool-credits">~8 credits/use</span>
                </div>
              </div>
            </div>
            
            {/* Credits Info */}
            <div className="credits-info">
              <h2>About Credits</h2>
              <p>
                Our tools use a credit-based system. You currently have 
                <strong> {user.credits || 0} credits</strong> in your account.
              </p>
              <p>
                New accounts receive <strong>50 free credits</strong> to get started. 
                Additional credits can be purchased when the tools launch.
              </p>
            </div>
            
            {/* Newsletter Signup */}
            <div className="notify-section">
              <h2>Get Notified</h2>
              <p>We'll let you know as soon as the tools are ready!</p>
              <div className="notify-status">
                <span className="check-icon">✅</span>
                <span>You'll be notified at <strong>{user.email}</strong></span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
```

---

### Auth Context/Hook (If Not Already Implemented)

```jsx
// context/AuthContext.jsx
import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('token'));
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    // Check if user is logged in on mount
    if (token) {
      fetchUser();
    } else {
      setLoading(false);
    }
  }, []);
  
  const fetchUser = async () => {
    try {
      const response = await fetch('https://api.smatatech.com.ng/api/auth/me', {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        setUser(data.data);
      } else {
        // Token invalid, clear it
        logout();
      }
    } catch (error) {
      logout();
    } finally {
      setLoading(false);
    }
  };
  
  const login = (newToken, userData) => {
    localStorage.setItem('token', newToken);
    setToken(newToken);
    setUser(userData);
  };
  
  const logout = () => {
    localStorage.removeItem('token');
    setToken(null);
    setUser(null);
  };
  
  return (
    <AuthContext.Provider value={{
      user,
      token,
      isAuthenticated: !!user,
      loading,
      login,
      logout,
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
```

---

### Suggested CSS Styles

```css
/* Tools Page Styles */
.tools-page {
  min-height: 100vh;
}

.tools-hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 80px 0;
  text-align: center;
}

.tools-hero h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.tools-hero .subtitle {
  font-size: 1.25rem;
  opacity: 0.9;
  max-width: 600px;
  margin: 0 auto 3rem;
}

.tools-preview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  max-width: 900px;
  margin: 0 auto;
}

.tool-preview-card {
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1.5rem;
}

.tool-preview-card .icon {
  font-size: 2rem;
  display: block;
  margin-bottom: 0.5rem;
}

.tools-auth-section {
  padding: 60px 0;
  background: #f8f9fa;
}

.auth-container {
  max-width: 450px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
  overflow: hidden;
}

.auth-tabs {
  display: flex;
  border-bottom: 1px solid #eee;
}

.auth-tabs .tab {
  flex: 1;
  padding: 1rem;
  border: none;
  background: none;
  cursor: pointer;
  font-weight: 600;
  color: #666;
  transition: all 0.3s;
}

.auth-tabs .tab.active {
  color: #667eea;
  border-bottom: 2px solid #667eea;
}

.auth-form {
  padding: 2rem;
}

.auth-form h2 {
  margin-bottom: 0.5rem;
}

.form-subtitle {
  color: #666;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.btn-primary {
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  background: #fee;
  color: #c00;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.field-error {
  color: #c00;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* Coming Soon Styles */
.tools-coming-soon {
  min-height: 100vh;
  background: #f8f9fa;
}

.user-header {
  background: white;
  border-bottom: 1px solid #eee;
  padding: 1rem 0;
}

.user-header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.credits-badge {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  margin-left: 1rem;
}

.coming-soon-hero {
  padding: 80px 0;
  text-align: center;
}

.icon-large {
  font-size: 5rem;
  margin-bottom: 1rem;
}

.coming-soon-content h1 {
  font-size: 3rem;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.lead {
  font-size: 1.25rem;
  color: #666;
  max-width: 600px;
  margin: 0 auto 2rem;
}

.tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  max-width: 1000px;
  margin: 2rem auto;
  text-align: left;
}

.tool-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s;
}

.tool-card:hover {
  transform: translateY(-5px);
}

.tool-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.tool-card h3 {
  margin-bottom: 0.5rem;
}

.tool-credits {
  display: inline-block;
  background: #f0f0f0;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  color: #666;
  margin-top: 0.5rem;
}

.notify-status {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #e8f5e9;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  color: #2e7d32;
}
```

---

## SECTION 7: IMPLEMENTATION CHECKLIST

### Phase 0: DATA MIGRATION (DO THIS FIRST!)
- [ ] Test API connection: `https://api.smatatech.com.ng/api/health`
- [ ] Login as admin to get authentication token
- [ ] Locate ALL mock/demo data files in frontend codebase
- [ ] Create migration script (`scripts/migrateDataToBackend.js`)
- [ ] Migrate Services → verify at `/api/services`
- [ ] Migrate Categories → verify at `/api/categories`
- [ ] Migrate Blog Posts → verify at `/api/posts`
- [ ] Migrate Case Studies → verify at `/api/case-studies`
- [ ] Migrate Testimonials → verify at `/api/testimonials`
- [ ] Migrate Brands → verify at `/api/brands`
- [ ] Update Site Settings → verify at `/api/settings`
- [ ] Handle image uploads (external URLs or upload to backend)
- [ ] Verify ALL data appears correctly via API
- [ ] Verify data in admin dashboard

### Phase 1: Setup & Configuration
- [ ] Configure API base URL: `https://api.smatatech.com.ng/api`
- [ ] Set up HTTP client with proper headers
- [ ] Implement token storage (localStorage/cookies)
- [ ] Create API service modules
- [ ] Test health endpoint connection

### Phase 2: Replace Mock Data with API Calls
- [ ] Identify ALL files that import/use mock data
- [ ] Replace static imports with API fetch calls
- [ ] Add loading states while fetching
- [ ] Add error handling for failed fetches
- [ ] ONLY AFTER API works: Remove mock data files
- [ ] Remove unused mock data imports
- [ ] Clean up any remaining hardcoded content

### Phase 3: Public Website Integration
- [ ] Fetch and display site settings
- [ ] Implement services listing and detail pages
- [ ] Implement case studies/portfolio
- [ ] Implement testimonials section
- [ ] Implement blog listing with pagination
- [ ] Implement blog post detail page
- [ ] Implement blog categories filter
- [ ] Implement brand logos section
- [ ] Implement contact form submission

### Phase 4: User Authentication
- [ ] Create login page/modal
- [ ] Create registration page/modal
- [ ] Implement forgot password flow
- [ ] Implement reset password page
- [ ] Store token securely
- [ ] Add auth state management
- [ ] Protect authenticated routes
- [ ] Implement logout functionality

### Phase 5: Admin Dashboard
- [ ] Create admin login page
- [ ] Implement admin auth flow
- [ ] Dashboard statistics display
- [ ] User management CRUD
- [ ] Blog posts CRUD + rich text editor
- [ ] Categories management
- [ ] Services management
- [ ] Case studies management
- [ ] Testimonials management
- [ ] Brands management
- [ ] Contact submissions view
- [ ] Comments moderation
- [ ] Site settings editor
- [ ] File upload functionality

### Phase 6: AI Tools Section
- [ ] Display available AI tools
- [ ] Show credit balance
- [ ] Implement tool execution
- [ ] Show usage history
- [ ] Handle insufficient credits

### Phase 7: Error Handling & UX
- [ ] Loading states for all API calls
- [ ] Error messages display
- [ ] Form validation feedback
- [ ] 404 pages for missing resources
- [ ] Network error handling
- [ ] Token expiration handling
- [ ] Rate limit error handling

### Phase 7: AI Tools Page (NEW FEATURE)
- [ ] Create `/tools` route/page
- [ ] Add "Tools" link to main navigation menu
- [ ] Implement unauthenticated state (login/register prompt)
- [ ] Implement login form on tools page
- [ ] Implement registration form on tools page
- [ ] Implement authenticated state (coming soon page)
- [ ] Store auth token after login/register
- [ ] Add logout functionality
- [ ] Test complete auth flow on tools page

### Phase 8: Final Polish
- [ ] Test all user flows
- [ ] Verify mobile responsiveness
- [ ] Check SEO meta tags from API
- [ ] Optimize images loading
- [ ] Add proper caching headers
- [ ] Performance testing

---

## SECTION 8: CODE PATTERNS

### API Service Example
```javascript
// services/api.js
const API_BASE = 'https://api.smatatech.com.ng/api';

class ApiService {
  constructor() {
    this.token = localStorage.getItem('token');
  }

  setToken(token) {
    this.token = token;
    localStorage.setItem('token', token);
  }

  clearToken() {
    this.token = null;
    localStorage.removeItem('token');
  }

  async request(endpoint, options = {}) {
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
      ...options,
      headers,
    });

    const data = await response.json();

    if (!response.ok) {
      if (response.status === 401) {
        this.clearToken();
        // Redirect to login
      }
      throw new ApiError(data.message, data.errors, response.status);
    }

    return data;
  }

  // Public endpoints
  getSettings = () => this.request('/settings');
  getServices = () => this.request('/services');
  getService = (slug) => this.request(`/services/${slug}`);
  getCaseStudies = (page = 1) => this.request(`/case-studies?page=${page}`);
  getCaseStudy = (slug) => this.request(`/case-studies/${slug}`);
  getTestimonials = () => this.request('/testimonials');
  getPosts = (params = {}) => this.request(`/posts?${new URLSearchParams(params)}`);
  getPost = (slug) => this.request(`/posts/${slug}`);
  getCategories = () => this.request('/categories');
  getBrands = () => this.request('/brands');
  submitContact = (data) => this.request('/contact', {
    method: 'POST',
    body: JSON.stringify(data),
  });

  // User auth
  register = (data) => this.request('/auth/register', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  login = (data) => this.request('/auth/login', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  logout = () => this.request('/auth/logout', { method: 'POST' });
  getMe = () => this.request('/auth/me');

  // Admin auth
  adminLogin = (data) => this.request('/admin/login', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  adminLogout = () => this.request('/admin/logout', { method: 'POST' });
  getAdminMe = () => this.request('/admin/me');

  // Admin CRUD helpers
  adminGet = (resource, id = '') => this.request(`/admin/${resource}${id ? '/' + id : ''}`);
  adminCreate = (resource, data) => this.request(`/admin/${resource}`, {
    method: 'POST',
    body: JSON.stringify(data),
  });
  adminUpdate = (resource, id, data) => this.request(`/admin/${resource}/${id}`, {
    method: 'PUT',
    body: JSON.stringify(data),
  });
  adminDelete = (resource, id) => this.request(`/admin/${resource}/${id}`, {
    method: 'DELETE',
  });
}

export const api = new ApiService();
```

### React Hook Example
```javascript
// hooks/useApi.js
import { useState, useEffect } from 'react';
import { api } from '../services/api';

export function useApi(fetcher, dependencies = []) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    let mounted = true;
    setLoading(true);

    fetcher()
      .then(response => {
        if (mounted) {
          setData(response.data);
          setError(null);
        }
      })
      .catch(err => {
        if (mounted) {
          setError(err.message);
        }
      })
      .finally(() => {
        if (mounted) {
          setLoading(false);
        }
      });

    return () => { mounted = false; };
  }, dependencies);

  return { data, loading, error, refetch: () => fetcher().then(r => setData(r.data)) };
}

// Usage
function ServicesPage() {
  const { data: services, loading, error } = useApi(() => api.getServices());

  if (loading) return <LoadingSpinner />;
  if (error) return <ErrorMessage message={error} />;

  return (
    <div>
      {services.map(service => (
        <ServiceCard key={service.id} service={service} />
      ))}
    </div>
  );
}
```

---

## SECTION 9: ERROR CODES REFERENCE

| Code | Meaning | Action |
|------|---------|--------|
| 200 | Success | Process response |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Check request format |
| 401 | Unauthenticated | Redirect to login |
| 403 | Forbidden | Show access denied |
| 404 | Not Found | Show 404 page |
| 422 | Validation Error | Show field errors |
| 429 | Rate Limited | Show retry message |
| 500 | Server Error | Show generic error |
| 503 | Service Unavailable | Show maintenance message |

---

## SECTION 10: RATE LIMITS

| Endpoint Type | Limit |
|--------------|-------|
| General API | 60/minute |
| Auth endpoints | 5/minute |
| Contact form | 3/minute |
| File uploads | 10/minute |

---

## FINAL NOTES

1. **Test the health endpoint first:** `https://api.smatatech.com.ng/api/health`
2. **Default admin credentials:** (Change after first login!)
   - Email: `admin@smatatech.com`
   - Password: `password`
3. **All image URLs** returned by the API are absolute URLs
4. **Pagination** uses `?page=1&per_page=15` format
5. **Dates** are in ISO 8601 format
6. **IDs** are UUIDs (strings)

---

## ⚠️ CRITICAL WORKFLOW - FOLLOW THIS ORDER!

```
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 1: MIGRATE DATA                                               │
│  ─────────────────────                                              │
│  • Find all mock/demo data in frontend                              │
│  • Run migration script to push data to backend API                 │
│  • Verify data exists via API endpoints                             │
│  • DO NOT delete mock data yet!                                     │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 2: CONNECT FRONTEND TO API                                    │
│  ──────────────────────────────                                     │
│  • Replace mock data imports with API fetch calls                   │
│  • Add loading states and error handling                            │
│  • Test each page/component works with API data                     │
│  • Keep mock data as fallback during development                    │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│  STEP 3: CLEANUP (ONLY AFTER EVERYTHING WORKS)                      │
│  ─────────────────────────────────────────────                      │
│  • Remove mock data files                                           │
│  • Remove unused imports                                            │
│  • Final testing                                                    │
│  • Deploy!                                                          │
└─────────────────────────────────────────────────────────────────────┘
```

### Summary of Data to Migrate:

| Content Type | Source (Frontend) | Destination (API) |
|--------------|-------------------|-------------------|
| Services | Mock services array | `POST /admin/services` |
| Categories | Mock categories | `POST /admin/categories` |
| Blog Posts | Mock posts array | `POST /admin/posts` |
| Case Studies | Mock portfolio items | `POST /admin/case-studies` |
| Testimonials | Mock testimonials | `POST /admin/testimonials` |
| Brand Logos | Mock brands/clients | `POST /admin/brands` |
| Site Settings | Config/constants | `PUT /admin/settings` |

### Verification Endpoints:

After migration, verify data at these PUBLIC endpoints:

| Content | Verification URL |
|---------|-----------------|
| Services | https://api.smatatech.com.ng/api/services |
| Categories | https://api.smatatech.com.ng/api/categories |
| Blog Posts | https://api.smatatech.com.ng/api/posts |
| Case Studies | https://api.smatatech.com.ng/api/case-studies |
| Testimonials | https://api.smatatech.com.ng/api/testimonials |
| Brands | https://api.smatatech.com.ng/api/brands |
| Settings | https://api.smatatech.com.ng/api/settings |
| Health | https://api.smatatech.com.ng/api/health |

---

**🚀 START NOW: MIGRATE DATA FIRST → THEN CONNECT API → THEN CLEANUP**
