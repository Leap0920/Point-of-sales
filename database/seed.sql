USE pos_db;

-- Seed roles
INSERT INTO roles (name) VALUES 
  ('Admin'),
  ('Cashier')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Default admin user (password: admin123 – change after first login)
INSERT INTO users (name, username, password_hash, role_id)
VALUES (
  'Administrator',
  'admin',
  '$2y$10$2ZFHqgCXlCbzNd7wx0jv7eKq9hR3.kX5JziWSi0JyoQo47FwAoXfK', -- password_hash('admin123')
  (SELECT id FROM roles WHERE name = 'Admin' LIMIT 1)
)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Default cashier user (password: cashier123)
INSERT INTO users (name, username, password_hash, role_id)
VALUES (
  'Cashier Employee',
  'cashier',
  '$2y$10$2ZFHqgCXlCbzNd7wx0jv7eKq9hR3.kX5JziWSi0JyoQo47FwAoXfK', -- password_hash('cashier123') - same hash for demo
  (SELECT id FROM roles WHERE name = 'Cashier' LIMIT 1)
)
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Sample categories
INSERT INTO categories (name) VALUES 
  ('Meals'),
  ('Drinks'),
  ('Desserts')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Sample products
INSERT INTO products (name, category_id, price, cost, stock)
VALUES
  ('Fried Chicken Meal', (SELECT id FROM categories WHERE name = 'Meals' LIMIT 1), 150.00, 90.00, 50),
  ('Burger Meal', (SELECT id FROM categories WHERE name = 'Meals' LIMIT 1), 120.00, 70.00, 40),
  ('Iced Tea', (SELECT id FROM categories WHERE name = 'Drinks' LIMIT 1), 35.00, 10.00, 100),
  ('Soft Drink', (SELECT id FROM categories WHERE name = 'Drinks' LIMIT 1), 30.00, 12.00, 100),
  ('Ice Cream', (SELECT id FROM categories WHERE name = 'Desserts' LIMIT 1), 60.00, 30.00, 30)
ON DUPLICATE KEY UPDATE price = VALUES(price), cost = VALUES(cost), stock = VALUES(stock);


