# Point-of-Sales System Documentation

## Overview
This document provides a comprehensive guide to the Point-of-Sales (POS) system, covering its main functions, tools, usage instructions, database structure, and key flows such as password recovery and admin signup.

---

## System Functions
- **Authentication**: Secure login for admin and cashier roles.
- **Product Management**: Add, update, and display products with images.
- **Sales Processing**: Handle transactions, calculate totals, and record sales.
- **User Management**: Admin can add, edit, and manage users.
- **Category Management**: Organize products by categories.
- **Reporting**: View transaction history and sales reports.

---

## Tools & Technologies
- **PHP**: Backend logic and server-side scripting.
- **MySQL**: Database management.
- **HTML/CSS**: Frontend structure and styling.
- **JavaScript**: Client-side interactivity (if present).
- **XAMPP**: Local development environment.

---

## How to Use the System
1. **Setup**: Import the database schema from `database/schema.sql` and seed data using `database/seed.sql`.
2. **Configuration**: Update database credentials in `config/config.php`.
3. **Access**: Open the system via `public/index.php` in your browser.
4. **Login**: Use `public/admin_login.php` or `public/cashier_login.php` for respective roles.
5. **Product & Category Management**: Accessible from the admin dashboard.
6. **Sales**: Cashier processes sales via `public/pos.php`.
7. **Reports**: Admin views transactions in `public/transactions.php`.

---

## Database Structure
- **users**: Stores user info (id, username, password, role, email).
- **products**: Product details (id, name, price, category, image).
- **categories**: Product categories.
- **transactions**: Sales records.
- **transaction_items**: Items per transaction.

Refer to `database/schema.sql` for full table definitions.

---

## Forgot Password Flow
1. **User clicks 'Forgot Password'** on the login page.
2. **Form Submission**: User enters their registered email in `public/forgot_password.php`.
3. **Verification**: System checks if the email exists in the `users` table.
4. **Password Update**: User sets a new password, which is updated in the database.

---

## Admin Signup Flow
1. **Access Signup Page**: Go to `public/signup.php`.
2. **Fill Form**: Enter admin details (username, password, email, etc.).
3. **Validation**: System checks for unique username/email.
4. **Database Insert**: New admin is added to the `users` table with role 'admin'.
5. **Redirect/Login**: Admin can now log in via `public/admin_login.php`.
6. **Sign up admin code** : use the 'ADMIN2024' for admin 

---

## Security Notes
- Passwords should be hashed before storing (check `app/Auth.php`).
- Validate all user inputs to prevent SQL injection.
- Use sessions for authentication.

---

## Additional Resources
- **Setup Instructions**: See `SETUP.md`.
- **UI Improvements**: See `UI_IMPROVEMENTS.md`.
- **Troubleshooting**: See `FIX_POS_REFRESHmd` and `README.md`.

---

## Contact & Support
For further assistance, contact the system administrator or refer to the documentation files in the project root.

---

*Last updated: December 3, 2025*
