-- ============================================================================
-- SMATATECH API - Database Seed Data
-- ============================================================================
-- This SQL file can be imported directly via phpMyAdmin on cPanel
-- Run this AFTER running migrations
-- ============================================================================

-- ============================================================================
-- CATEGORIES
-- ============================================================================
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`) VALUES
(UUID(), 'Web Development', 'web-development', 'Articles about web development, frameworks, and best practices.', 'active', NOW(), NOW()),
(UUID(), 'Mobile Apps', 'mobile-apps', 'iOS, Android, and cross-platform mobile development insights.', 'active', NOW(), NOW()),
(UUID(), 'UI/UX Design', 'ui-ux-design', 'Design principles, user experience, and interface design tips.', 'active', NOW(), NOW()),
(UUID(), 'Cloud & DevOps', 'cloud-devops', 'Cloud computing, deployment strategies, and DevOps practices.', 'active', NOW(), NOW()),
(UUID(), 'AI & Machine Learning', 'ai-machine-learning', 'Artificial intelligence, machine learning, and data science.', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================================================
-- SERVICES
-- ============================================================================
INSERT INTO `services` (`id`, `title`, `slug`, `short_description`, `full_description`, `icon`, `status`, `order`, `created_at`, `updated_at`) VALUES
(UUID(), 'Web Development', 'web-development', 'Custom web applications built with modern technologies.', 'We build scalable, secure, and high-performance web applications using Laravel, React, Vue.js, and other cutting-edge technologies. Our solutions are tailored to meet your specific business requirements.', 'code', 'published', 1, NOW(), NOW()),
(UUID(), 'Mobile App Development', 'mobile-app-development', 'Native and cross-platform mobile applications.', 'From iOS to Android, we develop mobile applications that deliver exceptional user experiences. Our team specializes in React Native, Flutter, and native development for both platforms.', 'smartphone', 'published', 2, NOW(), NOW()),
(UUID(), 'UI/UX Design', 'ui-ux-design', 'User-centered design that converts visitors into customers.', 'Our design team creates intuitive, visually stunning interfaces that enhance user engagement. We focus on usability, accessibility, and conversion optimization.', 'palette', 'published', 3, NOW(), NOW()),
(UUID(), 'Cloud Solutions', 'cloud-solutions', 'Scalable cloud infrastructure and deployment.', 'We help businesses migrate to the cloud and optimize their infrastructure. Our expertise includes AWS, Google Cloud, Azure, and containerization with Docker and Kubernetes.', 'cloud', 'published', 4, NOW(), NOW()),
(UUID(), 'API Development', 'api-development', 'RESTful and GraphQL APIs for seamless integrations.', 'We design and build robust APIs that power your applications and enable third-party integrations. Our APIs are secure, well-documented, and built to scale.', 'api', 'published', 5, NOW(), NOW()),
(UUID(), 'AI Integration', 'ai-integration', 'Integrate AI capabilities into your applications.', 'Leverage the power of artificial intelligence in your business. We integrate chatbots, machine learning models, and AI-powered features into existing systems.', 'brain', 'published', 6, NOW(), NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- ============================================================================
-- TESTIMONIALS
-- ============================================================================
INSERT INTO `testimonials` (`id`, `client_name`, `client_title`, `client_company`, `content`, `rating`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES
(UUID(), 'Sarah Johnson', 'CEO', 'TechRetail Inc.', 'Smatatech transformed our e-commerce platform completely. The team''s expertise in Laravel and Vue.js delivered a solution that exceeded our expectations. Our conversion rate increased by 150% within months.', 5, 'published', 1, NOW(), NOW()),
(UUID(), 'Michael Chen', 'CTO', 'FinanceFlow', 'The real-time dashboard they built handles our 50,000+ daily users flawlessly. Their technical skills and attention to detail are outstanding. Highly recommended for any fintech project.', 5, 'published', 1, NOW(), NOW()),
(UUID(), 'Dr. Emily Roberts', 'Medical Director', 'MediCare Solutions', 'Building a HIPAA-compliant app was our biggest concern, but Smatatech handled it expertly. Patient satisfaction has soared, and we''ve significantly reduced administrative costs.', 5, 'published', 1, NOW(), NOW()),
(UUID(), 'David Okonkwo', 'Founder', 'StartupHub Nigeria', 'They built our MVP in record time without compromising on quality. The team understands startup needs and delivers results that help you scale.', 5, 'published', 0, NOW(), NOW()),
(UUID(), 'Lisa Anderson', 'Product Manager', 'GlobalTech Solutions', 'Working with Smatatech was a breeze. They communicated clearly, met every deadline, and delivered a product our users love. We''ve already started our second project with them.', 5, 'published', 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE `client_name` = VALUES(`client_name`);

-- ============================================================================
-- BRANDS
-- ============================================================================
INSERT INTO `brands` (`id`, `name`, `website`, `status`, `order`, `created_at`, `updated_at`) VALUES
(UUID(), 'TechRetail Inc.', 'https://techretail.example.com', 'active', 1, NOW(), NOW()),
(UUID(), 'FinanceFlow', 'https://financeflow.example.com', 'active', 2, NOW(), NOW()),
(UUID(), 'MediCare Solutions', 'https://medicare.example.com', 'active', 3, NOW(), NOW()),
(UUID(), 'StartupHub Nigeria', 'https://startuphub.ng', 'active', 4, NOW(), NOW()),
(UUID(), 'GlobalTech Solutions', 'https://globaltech.example.com', 'active', 5, NOW(), NOW()),
(UUID(), 'Innovate Africa', 'https://innovateafrica.example.com', 'active', 6, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ============================================================================
-- CASE STUDIES
-- ============================================================================
INSERT INTO `case_studies` (`id`, `title`, `slug`, `client`, `industry`, `summary`, `challenge`, `solution`, `results`, `technologies`, `status`, `publish_date`, `created_at`, `updated_at`) VALUES
(UUID(), 'E-Commerce Platform Redesign', 'ecommerce-platform-redesign', 'TechRetail Inc.', 'E-Commerce', 'Complete redesign of a major e-commerce platform resulting in 150% increase in conversions.', 'The client''s existing platform was outdated, slow, and not mobile-friendly. Cart abandonment was at 78% and mobile users accounted for only 15% of sales.', 'We redesigned the entire user experience with a mobile-first approach, implemented a new Laravel-based backend with Redis caching, and optimized the checkout flow to reduce friction.', 'Within 6 months: 150% increase in conversions, 60% reduction in cart abandonment, mobile sales increased from 15% to 55% of total revenue.', '["Laravel", "Vue.js", "Redis", "AWS", "Stripe"]', 'published', DATE_SUB(NOW(), INTERVAL 2 MONTH), NOW(), NOW()),
(UUID(), 'Healthcare Mobile App', 'healthcare-mobile-app', 'MediCare Solutions', 'Healthcare', 'HIPAA-compliant mobile app for patient-doctor communication and appointment management.', 'The healthcare provider needed a secure platform for patient communication, appointment scheduling, and medical record access that complied with strict HIPAA regulations.', 'Developed a React Native mobile app with end-to-end encryption, secure authentication, and integration with existing EHR systems. Implemented real-time video consultations.', 'Reduced no-show appointments by 40%, patient satisfaction increased by 85%, and the client saved $200K annually in administrative costs.', '["React Native", "Node.js", "PostgreSQL", "WebRTC", "AWS HIPAA"]', 'published', DATE_SUB(NOW(), INTERVAL 1 MONTH), NOW(), NOW()),
(UUID(), 'Fintech Dashboard Platform', 'fintech-dashboard-platform', 'FinanceFlow', 'Financial Services', 'Real-time financial analytics dashboard serving 50,000+ daily active users.', 'The fintech startup needed a scalable platform to display real-time financial data, portfolio analytics, and market trends to thousands of concurrent users.', 'Built a microservices architecture with Laravel for the API layer, React for the frontend, and WebSocket connections for real-time data. Implemented horizontal scaling with Kubernetes.', 'Successfully handles 50,000+ DAUs with 99.9% uptime. Average page load time under 1 second. Scaled from 1,000 to 50,000 users in 8 months.', '["Laravel", "React", "WebSockets", "Kubernetes", "PostgreSQL", "Redis"]', 'published', DATE_SUB(NOW(), INTERVAL 3 WEEK), NOW(), NOW())
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- ============================================================================
-- SITE SETTINGS
-- ============================================================================
INSERT INTO `site_settings` (`id`, `key`, `value`, `type`, `group`, `created_at`, `updated_at`) VALUES
(UUID(), 'site_name', 'Smatatech Technologies', 'string', 'general', NOW(), NOW()),
(UUID(), 'site_description', 'Digital Solutions Company - Web Development, Mobile Apps, AI Integration', 'string', 'general', NOW(), NOW()),
(UUID(), 'contact_email', 'hello@smatatech.com.ng', 'string', 'contact', NOW(), NOW()),
(UUID(), 'contact_phone', '+234 XXX XXX XXXX', 'string', 'contact', NOW(), NOW()),
(UUID(), 'address', 'Lagos, Nigeria', 'string', 'contact', NOW(), NOW()),
(UUID(), 'social_links', '{"facebook":"https://facebook.com/smatatech","twitter":"https://twitter.com/smatatech","linkedin":"https://linkedin.com/company/smatatech","instagram":"https://instagram.com/smatatech"}', 'json', 'social', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- ============================================================================
-- CHATBOT CONFIG
-- ============================================================================
INSERT INTO `chatbot_configs` (`id`, `system_prompt`, `personality_tone`, `allowed_topics`, `restricted_topics`, `greeting_message`, `fallback_message`, `is_enabled`, `version_label`, `created_at`, `updated_at`) VALUES
(UUID(), 'You are a helpful assistant for Smatatech Technologies, a digital solutions company specializing in web development, AI solutions, and digital transformation.', 'professional', '["web development", "AI solutions", "pricing", "services", "contact"]', '["competitors", "internal processes"]', 'Hello! Welcome to Smatatech Technologies. How can I help you today?', 'I apologize, but I''m not sure how to help with that. Would you like to speak with our team directly?', 1, 'v1.0', NOW(), NOW())
ON DUPLICATE KEY UPDATE `system_prompt` = VALUES(`system_prompt`);

-- ============================================================================
-- Done!
-- ============================================================================
SELECT 'Sample data inserted successfully!' AS message;
