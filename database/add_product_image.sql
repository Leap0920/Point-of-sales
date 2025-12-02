-- Add image column to products table
USE pos_db;

ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER cost;