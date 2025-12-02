# Project Structure

## Directory Organization

```
Point-of-sales/
├── app/                    # Core application logic
│   ├── Auth.php           # Authentication class
│   ├── auth_only.php      # Auth middleware
│   └── Database.php       # Database connection singleton
├── config/                 # Configuration files
│   └── config.php         # Database and app settings
├── database/              # SQL scripts
│   ├── schema.sql         # Database schema
│   ├── seed.sql           # Initial data
│   └── add_cashier.sql    # Additional user setup
├── public/                # Web-accessible files
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── images/            # Static assets
│   ├── index.php          # Entry point (role-based redirect)
│   ├── login.php          # Login page
│   ├── logout.php         # Logout handler
│   ├── admin_dashboard.php # Admin back office
│   ├── pos.php            # Cashier POS terminal
│   ├── products.php       # Product management
│   ├── categories.php     # Category management
│   ├── users.php          # User management
│   └── process_sale.php   # Sales transaction API
└── views/                 # Reusable view templates
    └── layout.php         # Main layout wrapper
```

## File Naming Conventions
- **PHP files**: snake_case (e.g., `admin_dashboard.php`)
- **Classes**: PascalCase (e.g., `Auth`, `Database`)
- **Database tables**: snake_case (e.g., `sale_items`)

## Key Files

### Entry Points
- `public/index.php` - Main entry, redirects based on role
- `public/login.php` - Authentication page
- `public/admin_dashboard.php` - Admin interface
- `public/pos.php` - Cashier interface

### Core Classes
- `app/Auth.php` - Handles login, logout, session management
- `app/Database.php` - PDO connection singleton
- `app/auth_only.php` - Middleware to require authentication

### Configuration
- `config/config.php` - Database credentials and app settings

## Routing Pattern
- No routing framework - direct file access
- Role-based redirects in `index.php`
- Auth middleware via `require` statements

## Database Schema
- `roles` - User role definitions
- `users` - User accounts with role_id
- `categories` - Product categories
- `products` - Menu items/products
- `sales` - Transaction headers
- `sale_items` - Transaction line items

## CSS Architecture
- CSS variables for theming
- Gradient-based design system
- Responsive breakpoints
- Component-based class naming
