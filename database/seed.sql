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
  '$2y$10$dlqIBsiHcgLcuUxXBK05IeEcxKc1cmcWXfRi9UGBrmvU3zfBLpWUy', -- password_hash('admin123')
  (SELECT id FROM roles WHERE name = 'Admin' LIMIT 1)
)
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

-- Default cashier user (password: cashier123)
INSERT INTO users (name, username, password_hash, role_id)
VALUES (
  'Cashier Employee',
  'cashier',
  '$2y$10$DaebE9NVA0.TobFdJk/czeyvAfwEf0tMFvywhZGrr.tuhIGQG39se', -- password_hash('cashier123')
  (SELECT id FROM roles WHERE name = 'Cashier' LIMIT 1)
)
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

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
  ('Spaghetti Carbonara', (SELECT id FROM categories WHERE name = 'Meals' LIMIT 1), 180.00, 100.00, 35),
  ('Grilled Fish', (SELECT id FROM categories WHERE name = 'Meals' LIMIT 1), 200.00, 120.00, 25),
  ('Beef Steak', (SELECT id FROM categories WHERE name = 'Meals' LIMIT 1), 250.00, 150.00, 20),
  ('Iced Tea', (SELECT id FROM categories WHERE name = 'Drinks' LIMIT 1), 35.00, 10.00, 100),
  ('Soft Drink', (SELECT id FROM categories WHERE name = 'Drinks' LIMIT 1), 30.00, 12.00, 100),
  ('Fresh Juice', (SELECT id FROM categories WHERE name = 'Drinks' LIMIT 1), 50.00, 20.00, 60),
  ('Coffee', (SELECT id FROM categories WHERE name = 'Drinks' LIMIT 1), 45.00, 15.00, 80),
  ('Ice Cream', (SELECT id FROM categories WHERE name = 'Desserts' LIMIT 1), 60.00, 30.00, 30),
  ('Chocolate Cake', (SELECT id FROM categories WHERE name = 'Desserts' LIMIT 1), 80.00, 40.00, 25),
  ('Fruit Salad', (SELECT id FROM categories WHERE name = 'Desserts' LIMIT 1), 70.00, 35.00, 20)
ON DUPLICATE KEY UPDATE price = VALUES(price), cost = VALUES(cost), stock = VALUES(stock);

-- Sample sales transactions (past 30 days)
-- Get user IDs
SET @admin_id = (SELECT id FROM users WHERE username = 'admin' LIMIT 1);
SET @cashier_id = (SELECT id FROM users WHERE username = 'cashier' LIMIT 1);

-- Week 1 transactions
INSERT INTO sales (user_id, order_type, total_amount, cash_tendered, change_due, created_at) VALUES
(@cashier_id, 'dine-in', 335.00, 500.00, 165.00, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(@cashier_id, 'takeout', 270.00, 300.00, 30.00, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(@cashier_id, 'dine-in', 480.00, 500.00, 20.00, DATE_SUB(NOW(), INTERVAL 24 DAY)),
(@cashier_id, 'delivery', 390.00, 400.00, 10.00, DATE_SUB(NOW(), INTERVAL 24 DAY)),
(@cashier_id, 'dine-in', 215.00, 300.00, 85.00, DATE_SUB(NOW(), INTERVAL 23 DAY));

-- Week 2 transactions
INSERT INTO sales (user_id, order_type, total_amount, cash_tendered, change_due, created_at) VALUES
(@cashier_id, 'takeout', 420.00, 500.00, 80.00, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(@cashier_id, 'dine-in', 560.00, 600.00, 40.00, DATE_SUB(NOW(), INTERVAL 19 DAY)),
(@cashier_id, 'dine-in', 305.00, 400.00, 95.00, DATE_SUB(NOW(), INTERVAL 18 DAY)),
(@cashier_id, 'takeout', 180.00, 200.00, 20.00, DATE_SUB(NOW(), INTERVAL 17 DAY)),
(@cashier_id, 'delivery', 450.00, 500.00, 50.00, DATE_SUB(NOW(), INTERVAL 16 DAY));

-- Week 3 transactions
INSERT INTO sales (user_id, order_type, total_amount, cash_tendered, change_due, created_at) VALUES
(@cashier_id, 'dine-in', 620.00, 700.00, 80.00, DATE_SUB(NOW(), INTERVAL 13 DAY)),
(@cashier_id, 'takeout', 290.00, 300.00, 10.00, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(@cashier_id, 'dine-in', 385.00, 400.00, 15.00, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(@cashier_id, 'delivery', 510.00, 600.00, 90.00, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(@cashier_id, 'dine-in', 275.00, 300.00, 25.00, DATE_SUB(NOW(), INTERVAL 9 DAY));

-- Week 4 transactions (recent)
INSERT INTO sales (user_id, order_type, total_amount, cash_tendered, change_due, created_at) VALUES
(@cashier_id, 'takeout', 340.00, 400.00, 60.00, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(@cashier_id, 'dine-in', 475.00, 500.00, 25.00, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(@cashier_id, 'dine-in', 530.00, 600.00, 70.00, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(@cashier_id, 'takeout', 225.00, 300.00, 75.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(@cashier_id, 'delivery', 395.00, 400.00, 5.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(@cashier_id, 'dine-in', 410.00, 500.00, 90.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(@cashier_id, 'takeout', 285.00, 300.00, 15.00, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Sample sale items for the transactions (top sellers)
-- Get product IDs
SET @chicken_id = (SELECT id FROM products WHERE name = 'Fried Chicken Meal' LIMIT 1);
SET @burger_id = (SELECT id FROM products WHERE name = 'Burger Meal' LIMIT 1);
SET @spaghetti_id = (SELECT id FROM products WHERE name = 'Spaghetti Carbonara' LIMIT 1);
SET @fish_id = (SELECT id FROM products WHERE name = 'Grilled Fish' LIMIT 1);
SET @steak_id = (SELECT id FROM products WHERE name = 'Beef Steak' LIMIT 1);
SET @icedtea_id = (SELECT id FROM products WHERE name = 'Iced Tea' LIMIT 1);
SET @softdrink_id = (SELECT id FROM products WHERE name = 'Soft Drink' LIMIT 1);
SET @juice_id = (SELECT id FROM products WHERE name = 'Fresh Juice' LIMIT 1);
SET @coffee_id = (SELECT id FROM products WHERE name = 'Coffee' LIMIT 1);
SET @icecream_id = (SELECT id FROM products WHERE name = 'Ice Cream' LIMIT 1);
SET @cake_id = (SELECT id FROM products WHERE name = 'Chocolate Cake' LIMIT 1);

-- Sale 1 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(1, @chicken_id, 2, 150.00, 300.00),
(1, @icedtea_id, 1, 35.00, 35.00);

-- Sale 2 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(2, @burger_id, 2, 120.00, 240.00),
(2, @softdrink_id, 1, 30.00, 30.00);

-- Sale 3 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(3, @steak_id, 1, 250.00, 250.00),
(3, @spaghetti_id, 1, 180.00, 180.00),
(3, @juice_id, 1, 50.00, 50.00);

-- Sale 4 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(4, @fish_id, 1, 200.00, 200.00),
(4, @chicken_id, 1, 150.00, 150.00),
(4, @coffee_id, 1, 45.00, 45.00);

-- Sale 5 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(5, @burger_id, 1, 120.00, 120.00),
(5, @icedtea_id, 1, 35.00, 35.00),
(5, @icecream_id, 1, 60.00, 60.00);

-- Sale 6 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(6, @chicken_id, 2, 150.00, 300.00),
(6, @burger_id, 1, 120.00, 120.00);

-- Sale 7 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(7, @steak_id, 2, 250.00, 500.00),
(7, @icedtea_id, 2, 35.00, 70.00);

-- Sale 8 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(8, @spaghetti_id, 1, 180.00, 180.00),
(8, @burger_id, 1, 120.00, 120.00);

-- Sale 9 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(9, @chicken_id, 1, 150.00, 150.00),
(9, @softdrink_id, 1, 30.00, 30.00);

-- Sale 10 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(10, @fish_id, 2, 200.00, 400.00),
(10, @juice_id, 1, 50.00, 50.00);

-- Sale 11 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(11, @steak_id, 1, 250.00, 250.00),
(11, @chicken_id, 2, 150.00, 300.00),
(11, @coffee_id, 1, 45.00, 45.00),
(11, @cake_id, 1, 80.00, 80.00);

-- Sale 12 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(12, @burger_id, 2, 120.00, 240.00),
(12, @juice_id, 1, 50.00, 50.00);

-- Sale 13 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(13, @spaghetti_id, 2, 180.00, 360.00),
(13, @icedtea_id, 1, 35.00, 35.00);

-- Sale 14 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(14, @fish_id, 1, 200.00, 200.00),
(14, @chicken_id, 2, 150.00, 300.00),
(14, @softdrink_id, 1, 30.00, 30.00);

-- Sale 15 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(15, @burger_id, 2, 120.00, 240.00),
(15, @icedtea_id, 1, 35.00, 35.00);

-- Sale 16 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(16, @chicken_id, 2, 150.00, 300.00),
(16, @coffee_id, 1, 45.00, 45.00);

-- Sale 17 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(17, @steak_id, 1, 250.00, 250.00),
(17, @spaghetti_id, 1, 180.00, 180.00),
(17, @juice_id, 1, 50.00, 50.00);

-- Sale 18 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(18, @fish_id, 2, 200.00, 400.00),
(18, @burger_id, 1, 120.00, 120.00),
(18, @softdrink_id, 1, 30.00, 30.00);

-- Sale 19 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(19, @chicken_id, 1, 150.00, 150.00),
(19, @icedtea_id, 1, 35.00, 35.00),
(19, @cake_id, 1, 80.00, 80.00);

-- Sale 20 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(20, @burger_id, 3, 120.00, 360.00),
(20, @icedtea_id, 1, 35.00, 35.00);

-- Sale 21 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(21, @steak_id, 1, 250.00, 250.00),
(21, @chicken_id, 1, 150.00, 150.00),
(21, @coffee_id, 1, 45.00, 45.00);

-- Sale 22 items
INSERT INTO sale_items (sale_id, product_id, quantity, price, line_total) VALUES
(22, @spaghetti_id, 2, 180.00, 360.00),
(22, @juice_id, 2, 50.00, 100.00),
(22, @icecream_id, 1, 60.00, 60.00);


