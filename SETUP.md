# Point of Sale System - Setup Instructions

## Database Setup

1. **Import the database schema:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database called `pos_db` or use the existing one
   - Import `database/schema.sql`
   - Import `database/seed.sql`

2. **Verify the setup:**
   - Check that the `users` table has both admin and cashier accounts
   - Admin: username `admin`, password `admin123`
   - Cashier: username `cashier`, password `cashier123`

## Login Credentials

### Admin Account
- **Username:** admin
- **Password:** admin123
- **Access:** Full system access including:
  - Dashboard with analytics
  - Product management
  - Category management
  - User management
  - POS access

### Cashier Account
- **Username:** cashier
- **Password:** cashier123
- **Access:** POS terminal only
  - Product selection
  - Cart management
  - Checkout processing
  - Personal sales tracking

## Features

### Admin Dashboard
- Real-time sales statistics (today and monthly)
- Product inventory overview
- Low stock alerts
- Recent transactions list
- Top selling products
- Quick action buttons

### Cashier Dashboard (POS)
- Full-screen POS interface
- Product grid with categories
- Category filtering
- Shopping cart with quantity controls
- Real-time total calculation
- Checkout with cash handling
- Personal sales tracking

## Troubleshooting

### Can't login as admin?
1. Make sure you've run the `seed.sql` file
2. Check if the user exists in the database
3. The password hash should be: `$2y$10$2ZFHqgCXlCbzNd7wx0jv7eKq9hR3.kX5JziWSi0JyoQo47FwAoXfK`

### Can't see products in POS?
1. Make sure you've imported the seed data
2. Check that products have `is_active = 1`
3. Verify categories exist and are active

### Checkout not working?
1. Check browser console for errors
2. Verify `process_sale.php` exists
3. Make sure products have sufficient stock

## Next Steps

1. **Customize the system:**
   - Add more products via Products page
   - Create additional categories
   - Add more user accounts

2. **Test the workflow:**
   - Login as cashier
   - Add items to cart
   - Process a sale
   - Login as admin to see the transaction

3. **Security:**
   - Change default passwords immediately
   - Update database credentials in `config/config.php`
