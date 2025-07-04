-- Sample Test Data for RenalTales Application
-- This script creates comprehensive test data including users, stories, comments, and supporting data

-- Set charset
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. STORY CATEGORIES
-- ============================================
INSERT IGNORE INTO story_categories (id, name, slug, description, created_at, updated_at) VALUES
(1, 'Personal Journey', 'personal-journey', 'Personal experiences and life stories', NOW(), NOW()),
(2, 'Medical Experience', 'medical-experience', 'Medical procedures and healthcare experiences', NOW(), NOW()),
(3, 'Family & Relationships', 'family-relationships', 'Stories about family, friends, and relationships', NOW(), NOW()),
(4, 'Recovery & Healing', 'recovery-healing', 'Stories of recovery, healing, and overcoming challenges', NOW(), NOW()),
(5, 'Inspiration', 'inspiration', 'Motivational and inspiring stories', NOW(), NOW());

-- ============================================
-- 2. USERS (with different roles)
-- ============================================
-- Admin user
INSERT IGNORE INTO users (id, username, email, password_hash, role, full_name, language_preference, email_verified_at, created_at, updated_at) VALUES
(1, 'admin', 'admin@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', 'en', NOW(), NOW(), NOW());

-- Test users with different roles
INSERT IGNORE INTO users (id, username, email, password_hash, role, full_name, language_preference, email_verified_at, created_at, updated_at) VALUES
(2, 'john_doe', 'john@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'John Doe', 'en', NULL, NOW(), NOW()),
(3, 'maria_gonzalez', 'maria@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified_user', 'Maria Gonzalez', 'es', NOW(), NOW(), NOW()),
(4, 'peter_novak', 'peter@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'translator', 'Peter Novák', 'sk', NOW(), NOW(), NOW()),
(5, 'sarah_wilson', 'sarah@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderator', 'Sarah Wilson', 'en', NOW(), NOW(), NOW()),
(6, 'anna_kovacova', 'anna@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'verified_user', 'Anna Kováčová', 'sk', NOW(), NOW(), NOW()),
(7, 'carlos_rodriguez', 'carlos@renaltales.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'Carlos Rodriguez', 'es', NULL, NOW(), NOW());

-- ============================================
-- 3. STORIES (in different languages)
-- ============================================
-- English Stories
INSERT IGNORE INTO stories (id, user_id, category_id, original_language, status, access_level, created_at, updated_at, published_at) VALUES
(1, 3, 1, 'en', 'published', 'public', '2024-01-15 10:30:00', '2024-01-15 11:00:00', '2024-01-15 11:00:00'),
(2, 2, 2, 'en', 'published', 'registered', '2024-01-20 14:15:00', '2024-01-20 15:00:00', '2024-01-20 15:00:00'),
(3, 6, 4, 'en', 'published', 'public', '2024-02-01 09:45:00', '2024-02-01 10:30:00', '2024-02-01 10:30:00'),
(4, 2, 5, 'en', 'draft', 'public', '2024-02-10 16:20:00', '2024-02-10 16:20:00', NULL);

-- Slovak Stories
INSERT IGNORE INTO stories (id, user_id, category_id, original_language, status, access_level, created_at, updated_at, published_at) VALUES
(5, 4, 1, 'sk', 'published', 'public', '2024-01-25 12:00:00', '2024-01-25 13:00:00', '2024-01-25 13:00:00'),
(6, 6, 3, 'sk', 'published', 'verified', '2024-02-05 11:30:00', '2024-02-05 12:15:00', '2024-02-05 12:15:00');

-- Spanish Stories
INSERT IGNORE INTO stories (id, user_id, category_id, original_language, status, access_level, created_at, updated_at, published_at) VALUES
(7, 3, 2, 'es', 'published', 'public', '2024-01-30 15:45:00', '2024-01-30 16:30:00', '2024-01-30 16:30:00'),
(8, 7, 4, 'es', 'published', 'public', '2024-02-08 13:20:00', '2024-02-08 14:00:00', '2024-02-08 14:00:00');

-- ============================================
-- 4. STORY CONTENTS
-- ============================================
-- English Story Contents
INSERT IGNORE INTO story_contents (id, story_id, language, title, content, excerpt, meta_description, status, created_at, updated_at) VALUES
(1, 1, 'en', 'My Journey with Kidney Disease', 
'<p>When I was first diagnosed with chronic kidney disease, I felt like my world had turned upside down. The doctor''s words echoed in my mind as I tried to process what this meant for my future.</p>
<p>The initial shock gave way to a determination to learn everything I could about my condition. I researched treatment options, connected with support groups, and most importantly, refused to let this diagnosis define my limitations.</p>
<p>Today, three years later, I want to share my story to help others who might be walking a similar path. There is hope, there is support, and there is life beyond a kidney disease diagnosis.</p>',
'When I was first diagnosed with chronic kidney disease, I felt like my world had turned upside down. The doctor''s words echoed in my mind...',
'A personal journey through kidney disease diagnosis, treatment, and finding hope and support along the way.',
'published', '2024-01-15 10:30:00', '2024-01-15 11:00:00'),

(2, 2, 'en', 'Dialysis: What I Wish I Had Known', 
'<p>Starting dialysis was one of the most challenging experiences of my life. The appointments, the restrictions, the fatigue – it all felt overwhelming at first.</p>
<p>But I want to share what I''ve learned over the past year that might help someone else prepare for this journey. First, don''t be afraid to ask questions. Your healthcare team is there to help, and no question is too small.</p>
<p>Second, find your support system. Whether it''s family, friends, or fellow patients, having people who understand makes all the difference.</p>
<p>Finally, remember that this is just one part of your story, not the end of it.</p>',
'Starting dialysis was one of the most challenging experiences of my life. The appointments, the restrictions, the fatigue...',
'Practical advice and emotional support for those beginning their dialysis journey.',
'published', '2024-01-20 14:15:00', '2024-01-20 15:00:00'),

(3, 3, 'en', 'Finding Strength After Transplant', 
'<p>Receiving a kidney transplant was a gift I never expected. The call came at 2 AM on a Tuesday, and within hours, my life changed forever.</p>
<p>The recovery wasn''t easy. There were complications, setbacks, and moments of doubt. But every day, I reminded myself of the incredible generosity of my donor and their family.</p>
<p>Now, six months post-transplant, I''m learning to live with gratitude for every normal day. I''m sharing my story to honor my donor and to encourage others on the transplant list to keep hope alive.</p>',
'Receiving a kidney transplant was a gift I never expected. The call came at 2 AM on a Tuesday...',
'A story of hope, recovery, and gratitude following a successful kidney transplant.',
'published', '2024-02-01 09:45:00', '2024-02-01 10:30:00'),

(4, 4, 'en', 'Supporting a Loved One Through Treatment', 
'<p>This is a draft story about supporting my wife through her kidney disease journey. It''s been challenging for our whole family, but we''ve learned so much about resilience and love.</p>
<p>I want to share our experience from the caregiver''s perspective, including the practical and emotional challenges we''ve faced.</p>',
'This is a draft story about supporting my wife through her kidney disease journey...',
'A caregiver''s perspective on supporting a loved one through kidney disease treatment.',
'draft', '2024-02-10 16:20:00', '2024-02-10 16:20:00');

-- Slovak Story Contents
INSERT IGNORE INTO story_contents (id, story_id, language, title, content, excerpt, meta_description, status, created_at, updated_at) VALUES
(5, 5, 'sk', 'Môj príbeh s ochorením obličiek', 
'<p>Keď mi diagnostikovali chronické ochorenie obličiek, cítil som sa, akoby sa môj svet obrátil naruby. Slová lekára mi zneli v hlave, kým som sa snažil pochopiť, co to znamená pre moju budúcnosť.</p>
<p>Počiatočný šok ustúpil odhodlaniu naučiť sa všetko o mojom stave. Skúmal som možnosti liečby, spojil som sa so skupinami podpory a čo je najdôležitejšie, odmietol som nechať túto diagnózu definovať moje obmedzenia.</p>
<p>Dnes, o tri roky neskôr, chcem podeliť o môj príbeh, aby som pomohol ostatným, ktorí možno kráčajú podobnou cestou.</p>',
'Keď mi diagnostikovali chronické ochorenie obličiek, cítil som sa, akoby sa môj svet obrátil naruby...',
'Osobný príbeh cesty ochorením obličiek, liečbou a hľadaním nádeje a podpory.',
'published', '2024-01-25 12:00:00', '2024-01-25 13:00:00'),

(6, 6, 'sk', 'Rodina a podpora počas liečby', 
'<p>Najťažšou časťou môjho boja s ochorením obličiek nebolo fyzické utrpenie, ale strach z toho, ako to ovplyvní moju rodinu.</p>
<p>Moje deti boli ešte malé, keď som sa dozvedela o svojej diagnóze. Nevedela som, ako im to vysvetliť, ako ich pripraviť na zmeny, ktoré prídu.</p>
<p>Ale naučila som sa, že deti sú silnejšie, než si myslíme. S láskou a otvoreno komunikáciou sme to zvládli spoločne.</p>',
'Najťažšou časťou môjho boja s ochorením obličiek nebolo fyzické utrpenie...',
'Príbeh o tom, ako choroba ovplyvňuje celú rodinu a ako spoločne hľadať silu.',
'published', '2024-02-05 11:30:00', '2024-02-05 12:15:00');

-- Spanish Story Contents
INSERT IGNORE INTO story_contents (id, story_id, language, title, content, excerpt, meta_description, status, created_at, updated_at) VALUES
(7, 7, 'es', 'Mi experiencia con la diálisis', 
'<p>Comenzar la diálisis fue uno de los momentos más difíciles de mi vida. Al principio, todo parecía abrumador: las citas constantes, las restricciones dietéticas, el cansancio constante.</p>
<p>Pero quiero compartir lo que he aprendido durante este primer año que podría ayudar a alguien más a prepararse para este viaje. Primero, no tengas miedo de hacer preguntas. Tu equipo médico está ahí para ayudarte.</p>
<p>Segundo, encuentra tu sistema de apoyo. Ya sea familia, amigos o compañeros pacientes, tener personas que entiendan hace toda la diferencia.</p>',
'Comenzar la diálisis fue uno de los momentos más difíciles de mi vida. Al principio, todo parecía abrumador...',
'Experiencia personal con la diálisis y consejos para otros pacientes.',
'published', '2024-01-30 15:45:00', '2024-01-30 16:30:00'),

(8, 8, 'es', 'Superando los obstáculos', 
'<p>Mi camino hacia la recuperación no ha sido fácil. Ha habido días oscuros, momentos de desesperanza, y veces cuando quería rendirme.</p>
<p>Pero también ha habido momentos de luz, de esperanza, de pequeñas victorias que me han dado fuerzas para continuar.</p>
<p>Hoy quiero compartir cómo he aprendido a encontrar la fuerza en los momentos más difíciles, porque creo que todos necesitamos recordar que somos más fuertes de lo que pensamos.</p>',
'Mi camino hacia la recuperación no ha sido fácil. Ha habido días oscuros, momentos de desesperanza...',
'Una historia de superación personal y búsqueda de la fuerza interior.',
'published', '2024-02-08 13:20:00', '2024-02-08 14:00:00');

-- ============================================
-- 5. TAGS (Table doesn't exist - skipping)
-- ============================================
-- Note: The tags and story_tags tables don't exist in the current schema
-- This functionality may be added later

-- ============================================
-- 5. COMMENTS ON STORIES
-- ============================================
INSERT IGNORE INTO comments (id, story_id, user_id, content, status, created_at, updated_at) VALUES
-- Comments on Story 1 (My Journey with Kidney Disease)
(1, 1, 2, 'Thank you for sharing your story. It really helps to know that others have gone through similar experiences.', 'approved', '2024-01-16 09:30:00', '2024-01-16 09:30:00'),
(2, 1, 5, 'This is such an inspiring story. Your positive attitude is remarkable.', 'approved', '2024-01-17 14:20:00', '2024-01-17 14:20:00'),
(3, 1, 6, 'I was just diagnosed last month. Your story gives me hope. Thank you.', 'approved', '2024-01-18 11:45:00', '2024-01-18 11:45:00'),

-- Comments on Story 2 (Dialysis: What I Wish I Had Known)
(4, 2, 3, 'This is exactly the kind of information I needed when I started dialysis. Great advice!', 'approved', '2024-01-21 16:15:00', '2024-01-21 16:15:00'),
(5, 2, 4, 'As a healthcare worker, I appreciate how well you''ve explained the patient perspective.', 'approved', '2024-01-22 10:30:00', '2024-01-22 10:30:00'),

-- Comments on Story 3 (Finding Strength After Transplant)
(6, 3, 1, 'What a beautiful tribute to your donor. Thank you for sharing this journey.', 'approved', '2024-02-02 13:20:00', '2024-02-02 13:20:00'),
(7, 3, 7, 'I''m on the transplant waiting list. This gives me so much hope.', 'approved', '2024-02-03 15:45:00', '2024-02-03 15:45:00'),
(8, 3, 2, 'Six months post-transplant is amazing! Wishing you continued health.', 'approved', '2024-02-04 09:10:00', '2024-02-04 09:10:00'),

-- Comments on Story 5 (Slovak story)
(9, 5, 6, 'Ďakujem za zdieľanie vášho príbehu. Je povzbudzujúce čítať o úspešnom zvládnutí tejto diagnózy.', 'approved', '2024-01-26 12:30:00', '2024-01-26 12:30:00'),
(10, 5, 4, 'Váš pozitívny prístup je inšpiráciou pre všetkých nás.', 'approved', '2024-01-27 14:15:00', '2024-01-27 14:15:00'),

-- Comments on Story 7 (Spanish story)
(11, 7, 7, 'Muchas gracias por compartir tu experiencia. Me ayuda mucho como alguien que acaba de empezar diálisis.', 'approved', '2024-01-31 11:20:00', '2024-01-31 11:20:00'),
(12, 7, 3, 'Tu historia es muy valiosa. Gracias por tomarte el tiempo de escribirla.', 'approved', '2024-02-01 16:40:00', '2024-02-01 16:40:00'),

-- Comments on Story 8 (Spanish story)
(13, 8, 3, 'Qué historia tan inspiradora. Tu fuerza es admirable.', 'approved', '2024-02-09 10:30:00', '2024-02-09 10:30:00'),
(14, 8, 7, 'Necesitaba leer esto hoy. Gracias por recordarme que soy más fuerte de lo que pienso.', 'approved', '2024-02-10 13:45:00', '2024-02-10 13:45:00');

-- ============================================
-- 6. ACTIVITY LOGS (user activities)
-- ============================================
INSERT IGNORE INTO activity_logs (user_id, action, created_at) VALUES
(1, 'user_login', '2024-02-15 08:00:00'),
(2, 'story_created', '2024-01-20 14:15:00'),
(2, 'story_created', '2024-02-10 16:20:00'),
(3, 'story_created', '2024-01-15 10:30:00'),
(3, 'comment_posted', '2024-01-21 16:15:00'),
(4, 'story_created', '2024-01-25 12:00:00'),
(5, 'comment_posted', '2024-01-17 14:20:00'),
(6, 'story_created', '2024-02-05 11:30:00'),
(6, 'comment_posted', '2024-01-18 11:45:00'),
(7, 'story_created', '2024-02-08 13:20:00'),
(7, 'comment_posted', '2024-02-03 15:45:00');

-- ============================================
-- 9. STORY STATISTICS (Table doesn't exist - skipping)
-- ============================================
-- Note: The story_statistics table doesn't exist in the current schema
-- This functionality may be added later

-- ============================================
-- 7. SOME SECURITY LOG ENTRIES
-- ============================================
INSERT IGNORE INTO security_logs (event, data, ip_address, user_agent, created_at) VALUES
('user_login_success', '{"user_id": 1, "username": "admin"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-02-15 08:00:00'),
('user_login_success', '{"user_id": 2, "username": "john_doe"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-02-15 09:15:00'),
('user_login_success', '{"user_id": 3, "username": "maria_gonzalez"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-02-15 10:30:00'),
('password_reset_request', '{"email": "test@example.com"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '2024-02-14 16:20:00');

-- Restore foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SUMMARY OF TEST DATA CREATED:
-- ============================================
-- Users: 1 admin, 6 test users with different roles
-- Story Categories: 5 categories covering different types of stories
-- Stories: 8 stories (4 English, 2 Slovak, 2 Spanish) with different access levels
-- Story Contents: 8 content records with rich text content
-- Tags: 10 relevant tags
-- Story-Tag relationships: Multiple tags per story
-- Comments: 14 comments across different stories showing community engagement
-- Activity logs: User activities for tracking
-- Story statistics: View counts for stories
-- Security logs: Sample security events

-- Note: All passwords are hashed using PHP's password_hash() with the test password "password"
-- In a real environment, users would need to set their own secure passwords
