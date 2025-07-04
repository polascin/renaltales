-- Add full-text search indexes for story content
ALTER TABLE story_contents
ADD FULLTEXT INDEX ft_content (title, content, excerpt),
ADD FULLTEXT INDEX ft_title (title);

-- Add full-text search for comments
ALTER TABLE comments
ADD FULLTEXT INDEX ft_content (content);

-- Add full-text search for users
ALTER TABLE users
ADD FULLTEXT INDEX ft_user (username, full_name);

-- Add materialized view for story statistics
CREATE TABLE story_statistics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    total_comments INT UNSIGNED DEFAULT 0,
    total_translations INT UNSIGNED DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    view_count INT UNSIGNED DEFAULT 0,
    last_comment_at DATETIME,
    last_translation_at DATETIME,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    INDEX idx_views (view_count),
    INDEX idx_comments (total_comments),
    INDEX idx_translations (total_translations),
    INDEX idx_rating (average_rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add view count tracking table
CREATE TABLE story_views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_story_ip (story_id, ip_address),
    INDEX idx_viewed_at (viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add story ratings table
CREATE TABLE story_ratings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (story_id, user_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add table for tracking story reading progress
CREATE TABLE story_reading_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    progress_percentage TINYINT UNSIGNED DEFAULT 0,
    last_read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (story_id, user_id),
    INDEX idx_last_read (last_read_at),
    INDEX idx_completed (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add tags table for better story categorization
CREATE TABLE tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add story-tag relationship table
CREATE TABLE story_tags (
    story_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (story_id, tag_id),
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add trigger to update story statistics
DELIMITER //

CREATE TRIGGER after_comment_insert
AFTER INSERT ON comments
FOR EACH ROW
BEGIN
    INSERT INTO story_statistics (story_id, total_comments, last_comment_at)
    VALUES (NEW.story_id, 1, NEW.created_at)
    ON DUPLICATE KEY UPDATE
        total_comments = total_comments + 1,
        last_comment_at = NEW.created_at,
        updated_at = CURRENT_TIMESTAMP;
END;
//

CREATE TRIGGER after_translation_insert
AFTER INSERT ON story_contents
FOR EACH ROW
BEGIN
    INSERT INTO story_statistics (story_id, total_translations, last_translation_at)
    VALUES (NEW.story_id, 1, NEW.created_at)
    ON DUPLICATE KEY UPDATE
        total_translations = total_translations + 1,
        last_translation_at = NEW.created_at,
        updated_at = CURRENT_TIMESTAMP;
END;
//

CREATE TRIGGER after_rating_insert
AFTER INSERT ON story_ratings
FOR EACH ROW
BEGIN
    UPDATE story_statistics
    SET average_rating = (
        SELECT AVG(rating)
        FROM story_ratings
        WHERE story_id = NEW.story_id
    ),
    updated_at = CURRENT_TIMESTAMP
    WHERE story_id = NEW.story_id;
END;
//

CREATE TRIGGER after_rating_update
AFTER UPDATE ON story_ratings
FOR EACH ROW
BEGIN
    UPDATE story_statistics
    SET average_rating = (
        SELECT AVG(rating)
        FROM story_ratings
        WHERE story_id = NEW.story_id
    ),
    updated_at = CURRENT_TIMESTAMP
    WHERE story_id = NEW.story_id;
END;
//

DELIMITER ;
