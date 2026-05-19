-- Veritabani olusturma
CREATE DATABASE IF NOT EXISTS motor_alsana CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE motor_alsana;

-- Kullanicilar tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ilanlar tablosu
CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    brand VARCHAR(100) NOT NULL DEFAULT '',
    model VARCHAR(100) NOT NULL DEFAULT '',
    model_year SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    bike_type VARCHAR(80) NOT NULL DEFAULT '',
    mileage_km INT UNSIGNED NOT NULL DEFAULT 0,
    engine_cc INT UNSIGNED NOT NULL DEFAULT 0,
    horsepower SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    cylinder_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    color VARCHAR(50) NOT NULL DEFAULT '',
    origin_country VARCHAR(80) NOT NULL DEFAULT '',
    price DECIMAL(12,2) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_brand (brand),
    INDEX idx_bike_type (bike_type),
    INDEX idx_price (price),
    INDEX idx_model_year (model_year),
    INDEX idx_engine_cc (engine_cc),
    INDEX idx_mileage_km (mileage_km)
);

-- Mevcut tabloya yeni kolonlar eklemek icin (guncelleme scripti)
-- ALTER TABLE listings
--     ADD COLUMN brand VARCHAR(100) NOT NULL DEFAULT '' AFTER title,
--     ADD COLUMN model_year SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER model,
--     ADD COLUMN horsepower SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER engine_cc,
--     ADD COLUMN cylinder_count TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER horsepower,
--     ADD COLUMN color VARCHAR(50) NOT NULL DEFAULT '' AFTER cylinder_count,
--     ADD COLUMN origin_country VARCHAR(80) NOT NULL DEFAULT '' AFTER color,
--     ADD INDEX idx_brand (brand),
--     ADD INDEX idx_bike_type (bike_type),
--     ADD INDEX idx_price (price),
--     ADD INDEX idx_model_year (model_year),
--     ADD INDEX idx_engine_cc (engine_cc),
--     ADD INDEX idx_mileage_km (mileage_km);

-- Favoriler tablosu
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    last_price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_listing (user_id, listing_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_listing FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);
