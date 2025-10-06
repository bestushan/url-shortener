CREATE DATABASE url_shortener;
USE url_shortener;

CREATE TABLE urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_url VARCHAR(1000) NOT NULL,
    short_code VARCHAR(10) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    click_count INT DEFAULT 0
);

-- Run this in your database
ALTER TABLE urls ADD COLUMN is_custom BOOLEAN DEFAULT FALSE;