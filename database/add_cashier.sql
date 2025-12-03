-- Quick fix to add cashier user if missing
USE pos_db;

-- Add cashier user (password: cashier123)
INSERT INTO users (name, username, password_hash, role_id, is_active)
SELECT 'Cashier Employee', 'cashier', '$2y$10$2ZFHqgCXlCbzNd7wx0jv7eKq9hR3.kX5JziWSi0JyoQo47FwAoXfK', 
       (SELECT id FROM roles WHERE name = 'Cashier' LIMIT 1), 1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'cashier');

-- Verify users
SELECT u.id, u.name, u.username, r.name as role, u.is_active 
FROM users u 
JOIN roles r ON u.role_id = r.id;
