-- RenalTales Database Schema
-- Framework-less PHP application database structure

-- Create database
CREATE DATABASE IF NOT EXISTS renaltales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE renaltales;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    role ENUM('user', 'verified_user', 'translator', 'moderator', 'admin') DEFAULT 'user',
    active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    bio TEXT,
    avatar VARCHAR(255),
    language_preference VARCHAR(5) DEFAULT 'en',
    kidney_condition TEXT,
    timezone VARCHAR(50) DEFAULT 'UTC',
    privacy_settings JSON,
    last_login_at TIMESTAMP NULL,
    email_verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    password_reset_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (active),
    INDEX idx_created_at (created_at)
);

-- Stories table
CREATE TABLE stories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    category VARCHAR(50) NOT NULL,
    language VARCHAR(5) DEFAULT 'en',
    status ENUM('draft', 'pending', 'published', 'archived') DEFAULT 'draft',
    access_level ENUM('public', 'registered', 'verified', 'premium', 'translator', 'moderator', 'admin') DEFAULT 'public',
    featured TINYINT(1) DEFAULT 0,
    featured_image VARCHAR(255),
    tags JSON,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    comment_count INT DEFAULT 0,
    translation_count INT DEFAULT 0,
    original_story_id INT NULL, -- For translations
    seo_title VARCHAR(255),
    seo_description TEXT,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (original_story_id) REFERENCES stories(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_language (language),
    INDEX idx_status (status),
    INDEX idx_access_level (access_level),
    INDEX idx_featured (featured),
    INDEX idx_published_at (published_at),
    INDEX idx_created_at (created_at),
    FULLTEXT idx_search (title, content, excerpt)
);

-- Comments table
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    story_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL, -- For threaded comments
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'spam') DEFAULT 'approved',
    like_count INT DEFAULT 0,
    report_count INT DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_story_id (story_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Translations table (for story translations)
CREATE TABLE translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    story_id INT NOT NULL,
    translator_id INT NOT NULL,
    language VARCHAR(5) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    status ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
    quality_score DECIMAL(3,2) DEFAULT 0.00, -- For translation quality rating
    notes TEXT, -- Translator or moderator notes
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (translator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_story_id (story_id),
    INDEX idx_translator_id (translator_id),
    INDEX idx_language (language),
    INDEX idx_status (status),
    UNIQUE KEY unique_story_language (story_id, language)
);

-- Categories table (for dynamic categories)
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(7), -- Hex color code
    sort_order INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order),
    INDEX idx_active (active)
);

-- User sessions table (for session management)
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Security logs table
CREATE TABLE security_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event VARCHAR(100) NOT NULL,
    data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_event (event),
    INDEX idx_created_at (created_at)
);

-- Rate limiting table
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL, -- IP address or user ID
    created_at INT NOT NULL, -- Unix timestamp for easier cleanup
    
    INDEX idx_identifier (identifier),
    INDEX idx_created_at (created_at)
);

-- Login attempts table
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL, -- Email or IP address
    success TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_identifier (identifier),
    INDEX idx_created_at (created_at)
);

-- Story likes table
CREATE TABLE story_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    story_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_story_user (story_id, user_id),
    INDEX idx_story_id (story_id),
    INDEX idx_user_id (user_id)
);

-- Comment likes table
CREATE TABLE comment_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_comment_user (comment_id, user_id),
    INDEX idx_comment_id (comment_id),
    INDEX idx_user_id (user_id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'comment', 'like', 'translation', 'mention', etc.
    title VARCHAR(255) NOT NULL,
    message TEXT,
    data JSON, -- Additional notification data
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_read_at (read_at),
    INDEX idx_created_at (created_at)
);

-- User follows table (for following other users)
CREATE TABLE user_follows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    INDEX idx_follower_id (follower_id),
    INDEX idx_following_id (following_id)
);

-- Story bookmarks table
CREATE TABLE story_bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    story_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, story_id),
    INDEX idx_user_id (user_id),
    INDEX idx_story_id (story_id)
);

-- Tags table
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_usage_count (usage_count)
);

-- Story tags pivot table
CREATE TABLE story_tags (
    story_id INT NOT NULL,
    tag_id INT NOT NULL,
    
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (story_id, tag_id),
    INDEX idx_story_id (story_id),
    INDEX idx_tag_id (tag_id)
);

-- Settings table (for application settings)
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key_name (key_name)
);

-- Insert default categories
INSERT INTO categories (slug, name, description, icon, sort_order) VALUES
('general', 'General Stories', 'General experiences and stories', 'fas fa-book', 1),
('diagnosis', 'Diagnosis Stories', 'Stories about receiving a kidney diagnosis', 'fas fa-stethoscope', 2),
('dialysis', 'Life on Dialysis', 'Experiences with dialysis treatment', 'fas fa-heartbeat', 3),
('pre_transplant', 'Pre-Transplant', 'Journey before kidney transplant', 'fas fa-hourglass-half', 4),
('post_transplant', 'Post-Transplant', 'Life after kidney transplant', 'fas fa-heart', 5),
('lifestyle', 'Lifestyle Changes', 'Adapting lifestyle for kidney health', 'fas fa-running', 6),
('nutrition', 'Nutrition & Diet', 'Diet and nutrition for kidney health', 'fas fa-apple-alt', 7),
('mental_health', 'Mental Health', 'Dealing with emotional aspects', 'fas fa-brain', 8),
('success_stories', 'Success Stories', 'Inspiring success stories', 'fas fa-star', 9),
('family', 'Family Support', 'Family and caregivers experiences', 'fas fa-users', 10),
('coping', 'Coping Strategies', 'Ways to cope with challenges', 'fas fa-hands-helping', 11),
('medical', 'Medical Experiences', 'Medical procedures and treatments', 'fas fa-user-md', 12),
('hope', 'Hope & Inspiration', 'Stories of hope and inspiration', 'fas fa-sun', 13),
('challenges', 'Daily Challenges', 'Everyday challenges and solutions', 'fas fa-mountain', 14),
('community', 'Community Support', 'Community and peer support', 'fas fa-handshake', 15);

-- Insert default settings
INSERT INTO settings (key_name, value, type, description) VALUES
('site_name', 'RenalTales', 'string', 'Site name'),
('site_description', 'A community platform for people with kidney disorders', 'string', 'Site description'),
('admin_email', 'admin@renaltales.com', 'string', 'Administrator email'),
('registration_enabled', '1', 'boolean', 'Enable user registration'),
('story_moderation', '1', 'boolean', 'Enable story moderation'),
('comment_moderation', '0', 'boolean', 'Enable comment moderation'),
('max_story_length', '50000', 'integer', 'Maximum story length in characters'),
('stories_per_page', '12', 'integer', 'Number of stories per page'),
('featured_stories_count', '6', 'integer', 'Number of featured stories on homepage');

-- Create admin user (password: admin123456 - CHANGE THIS!)
INSERT INTO users (username, email, password_hash, first_name, last_name, role, email_verified, created_at) VALUES
('admin', 'admin@renaltales.com', '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$hash_here', 'Admin', 'User', 'admin', 1, NOW());

-- Create indexes for better performance
CREATE INDEX idx_stories_featured_published ON stories (featured, status, published_at);
CREATE INDEX idx_stories_category_published ON stories (category, status, published_at);
CREATE INDEX idx_comments_story_status ON comments (story_id, status, created_at);
CREATE INDEX idx_translations_story_status ON translations (story_id, status);

-- Create views for common queries
CREATE VIEW public_stories AS
SELECT s.*, u.username, u.first_name, u.last_name, u.avatar,
       (SELECT COUNT(*) FROM comments c WHERE c.story_id = s.id AND c.status = 'approved') as comment_count,
       (SELECT COUNT(*) FROM story_likes sl WHERE sl.story_id = s.id) as like_count
FROM stories s
JOIN users u ON s.user_id = u.id
WHERE s.status = 'published' AND s.access_level = 'public';

CREATE VIEW featured_stories AS
SELECT * FROM public_stories
WHERE featured = 1
ORDER BY published_at DESC;

-- Add triggers for updating counts
DELIMITER //

CREATE TRIGGER update_story_comment_count 
AFTER INSERT ON comments
FOR EACH ROW
BEGIN
    UPDATE stories 
    SET comment_count = (
        SELECT COUNT(*) FROM comments 
        WHERE story_id = NEW.story_id AND status = 'approved'
    ) 
    WHERE id = NEW.story_id;
END//

CREATE TRIGGER update_story_like_count 
AFTER INSERT ON story_likes
FOR EACH ROW
BEGIN
    UPDATE stories 
    SET like_count = (
        SELECT COUNT(*) FROM story_likes 
        WHERE story_id = NEW.story_id
    ) 
    WHERE id = NEW.story_id;
END//

CREATE TRIGGER update_comment_like_count 
AFTER INSERT ON comment_likes
FOR EACH ROW
BEGIN
    UPDATE comments 
    SET like_count = (
        SELECT COUNT(*) FROM comment_likes 
        WHERE comment_id = NEW.comment_id
    ) 
    WHERE id = NEW.comment_id;
END//

DELIMITER ;
