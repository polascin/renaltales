-- Remember Tokens Table Migration
-- Created: 2025-01-24
-- Description: Create table for storing remember me tokens

CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_token (user_id),
    KEY idx_token_hash (token_hash),
    KEY idx_expires_at (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add index for cleanup operations
CREATE INDEX idx_remember_tokens_expires ON remember_tokens(expires_at);
