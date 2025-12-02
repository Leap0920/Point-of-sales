# 🍽️ Restaurant Point of Sale (POS) System

A comprehensive web-based point of sale system designed specifically for restaurant operations with role-based access control, inventory management, and sales analytics.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [User Roles](#user-roles)
- [System Architecture](#system-architecture)
- [File Structure](#file-structure)
- [API Endpoints](#api-endpoints)
- [Security Features](#security-features)
- [Usage Guide](#usage-guide)
- [Screenshots](#screenshots)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

This Restaurant POS System is a modern, web-based solution that enables restaurants to efficiently manage their daily operations. The system provides separate interfaces for administrators and cashiers, ensuring appropriate access levels while maintaining data security and operational efficiency.

### Key Objectives
- **Streamline Operations**: Fast transaction processing for busy restaurant environments
- **Inventory Management**: Real-time stock tracking with low-stock alerts
- **Sales Analytics**: Comprehensive reporting and business insights
- **User Management**: Role-based access control for security
- **Modern Interface**: Intuitive, responsive design for all devices

## ✨ Features

### 🏪 Point of Sale Terminal
- **Fast Transaction Processing**: Optimized for quick order entry
- **Product Grid Display**: Visual product selection with images
- **Category Filtering**: Quick product navigation
- **Real-time Cart Management**: Add, remove, and modify quantities
- **Receipt Generation**: Professional receipts with print functionality
- **Stock Validation**: Prevents overselling with real-time stock checks

### 👨‍💼 Admin Dashboard
- **Business Analytics**: Daily and monthly sales reports
- **Top Products Tracking**: Identify best-selling items
- **Recent Transactions**: Monitor latest sales activity
- **User Activity Monitoring**: Track cashier performance

### 📦 Inventory Management
- **Product Management**: Add, edit, and manage menu items
- **Image Upload**: Visual product representation
- **Category Organization**: Structured product categorization
- **Stock Tracking**: Real-time inventory levels
- **Low Stock Alerts**: Automatic notifications for restocking

### 👥 User Management
- **Role-Based Access**: Admin and Cashier roles
- **User Statistics**: Active users and role distribution
- **Account Management**: Create, edit, and deactivate users
- **Activity Tracking**: Monitor user login and sales activity

### 🔐 Authentication System
- **Secure Login**: Separate login portals for different roles
- **Admin Registration**: Secure signup with admin codes
- **Password Reset**: Multi-step password recovery process
- **Session Management**: Secure session handling

### 📊 Transaction Management
- **Complete Transaction History**: Detailed sales records
- **Transaction Details**: Item-level breakdown for each sale
- **Pagination**: Efficient handling of large datasets
- **Search and Filter**: Find specific transactions quickly

## 🛠️ Technology Stack

### Backend
- **Language**: PHP 7.4+
- **Database**: MySQL 8.0 (via XAMPP)
- **Server**: Apache (XAMPP)
- **Session Management**: PHP native sessions
- **Database Access**: PDO with prepared statements

### Frontend
- **CSS Framework**: Bootstrap 5.3.3
- **Custom CSS**: Modern design system with CSS variables
- **JavaScript**: Vanilla JS (no framework dependencies)
- **Font**: Inter (Google Fonts)
- **Icons**: Emoji-based icon system

### Architecture Patterns
- **MVC-inspired**: Separation of concerns with app/, views/, public/
- **Singleton Pattern**: Database connection management
- **Authentication**: Custom Auth class with session management
- **Configuration**: Centralized config file

## 🚀 Installation

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser (Chrome, Firefox, Safari, Edge)
- Text editor (VS Code, Sublime Text, etc.)

### Step 1: Download and Setup XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP and start Apache and MySQL services
3. Verify installation by visiting `http://localhost`

### Step 2: Clone/Download Project
```bash
# Clone the repository (if using Git)
git clone [repository-url]

# Or download and extract the ZIP file to:
C:\xampp\htdocs\Point-of-sales\
```

### Step 3: Configure Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `pos_db`
3. Import the database schema:
   - Go to the `pos_db` database
   - Click "Import" tab
   - Select `database/schema.sql`
   - Click "Go"
4. Import sample data:
   - Select `database/seed.sql`
   - Click "Go"

### Step 4: Add Product Images Column
Run the migration to add image support:
```sql
ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER cost;
```

### Step 5: Configure Application
1. Update database credentials in `config/config.php` if needed
2. Ensure the `public/images/products/` directory exists and is writable

### Step 6: Access the Application
- **Main Entry**: `http://localhost/Point-of-sales/public/`
- **Admin Login**: `http://localhost/Point-of-sales/public/admin_login.php`
- **Cashier Login**: `http://localhost/Point-of-sales/public/cashier_login.php`

## 🗄️ Database Setup

### Database Schema
The system uses a relational database with the following main tables:

#### Core Tables
- **`roles`**: User role definitions (Admin, Cashier)
- **`users`**: User accounts with role assignments
- **`categories`**: Product categories for organization
- **`products`**: Menu items with pricing and inventory
- **`sales`**: Transaction headers
- **`sale_items`**: Transaction line items

### Default Credentials
After running the seed data:

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Cashier Account:**
- Username: `cashier`
- Password: `cashier123`

### Database Migration
To add the image column to existing installations:
```sql
-- Run this in phpMyAdmin SQL tab
USE pos_db;
ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER cost;
```

## 👤 User Roles

### 🔴 Administrator
**Full System Access:**
- Dashboard with analytics and reports
- Product management (add, edit, delete, images)
- Category management
- User management (create, edit, deactivate)
- Transaction history and details
- POS terminal access
- System configuration

**Permissions:**
- Create and manage user accounts
- Access all system features
- View all transaction data
- Modify system settings

### 🔵 Cashier
**Limited POS Access:**
- Point of Sale terminal only
- Process sales transactions
- View own transaction history
- Generate receipts

**Restrictions:**
- Cannot access admin dashboard
- Cannot manage products or users
- Cannot view other cashiers' data
- Cannot modify system settings

## 🏗️ System Architecture

### Directory Structure
```
Point-of-sales/
├── app/                    # Core application logic
│   ├── Auth.php           # Authentication class
│   ├── auth_only.php      # Auth middleware
│   └── Database.php       # Database connection singleton
├── config/                 # Configuration files
│   └── config.php         # Database and app settings
├── database/              # SQL scripts and migrations
│   ├── schema.sql         # Database schema
│   ├── seed.sql           # Initial data
│   └── add_product_image.sql # Image column migration
├── public/                # Web-accessible files
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── images/            # Static assets
│   │   └── products/      # Product images
│   ├── api/               # API endpoints
│   ├── index.php          # Entry point (role-based redirect)
│   ├── login.php          # Login page
│   ├── admin_login.php    # Admin login
│   ├── cashier_login.php  # Cashier login
│   ├── signup.php         # Admin registration
│   ├── forgot_password.php # Password reset
│   ├── admin_dashboard.php # Admin interface
│   ├── pos.php            # POS terminal
│   ├── products.php       # Product management
│   ├── categories.php     # Category management
│   ├── users.php          # User management
│   ├── transactions.php   # Transaction history
│   └── process_sale.php   # Sales processing API
└── views/                 # Reusable view templates
    └── layout.php         # Main layout wrapper
```

### Key Components

#### Authentication System (`app/Auth.php`)
- Session-based authentication
- Role-based access control
- Password hashing and verification
- Login/logout functionality

#### Database Layer (`app/Database.php`)
- PDO connection singleton
- Prepared statements for security
- UTF-8 character set support
- Error handling and logging

#### Layout System (`views/layout.php`)
- Consistent navigation across pages
- Role-based menu rendering
- Flash message system
- Responsive design framework

## 🔌 API Endpoints

### Sales Processing
**POST** `/public/process_sale.php`
```json
{
  "items": [
    {
      "id": 1,
      "name": "Coffee",
      "price": 45.00,
      "quantity": 2
    }
  ],
  "total": 90.00,
  "cash": 100.00,
  "change": 10.00
}
```

**Response:**
```json
{
  "success": true,
  "sale_id": 123,
  "message": "Sale processed successfully"
}
```

### Product Data
**GET** `/public/api/get_products.php`
```json
{
  "success": true,
  "products": [
    {
      "id": 1,
      "name": "Coffee",
      "price": "45.00",
      "stock": 50,
      "category_name": "Beverages",
      "image": "coffee_123.jpg"
    }
  ]
}
```

## 🔒 Security Features

### Authentication & Authorization
- **Password Hashing**: `password_hash()` with `PASSWORD_DEFAULT`
- **Session Management**: Secure PHP sessions with regeneration
- **Role-Based Access**: Middleware protection for admin routes
- **CSRF Protection**: Form token validation (recommended addition)

### Database Security
- **Prepared Statements**: All queries use PDO prepared statements
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Parameterized queries throughout
- **XSS Protection**: `htmlspecialchars()` for output escaping

### File Upload Security
- **File Type Validation**: Only JPG, PNG, GIF allowed
- **File Size Limits**: Maximum 2MB per image
- **Unique Filenames**: Prevents file conflicts and overwrites
- **Directory Restrictions**: Uploads confined to specific directories

### Access Control
- **Route Protection**: Auth middleware on sensitive pages
- **Role Verification**: Admin-only routes check user role
- **Session Validation**: Active session required for all operations
- **Logout Security**: Proper session destruction

## 📖 Usage Guide

### For Administrators

#### 1. Initial Setup
1. Access admin login: `http://localhost/Point-of-sales/public/admin_login.php`
2. Login with default credentials (admin/admin123)
3. Change default password immediately
4. Create additional admin accounts if needed

#### 2. Product Management
1. Navigate to **Products** from the main menu
2. Click **"Add New Product"** to create items
3. Fill in product details:
   - Product name
   - Category selection
   - Price
   - Stock quantity
   - Product image (optional)
4. Save and activate products

#### 3. Category Management
1. Go to **Categories** section
2. Create logical groupings (Beverages, Main Dishes, Desserts)
3. Organize products by category for easier POS navigation

#### 4. User Management
1. Access **Users** from the menu
2. Create cashier accounts:
   - Full name
   - Username
   - Password
   - Role assignment
3. Monitor user activity and manage permissions

#### 5. Sales Analytics
1. View dashboard for key metrics:
   - Daily sales totals
   - Monthly performance
   - Top-selling products
   - Recent transactions
2. Access detailed transaction history
3. Generate reports for business analysis

### For Cashiers

#### 1. Login Process
1. Access cashier login: `http://localhost/Point-of-sales/public/cashier_login.php`
2. Enter provided credentials
3. Automatic redirect to POS terminal

#### 2. Processing Sales
1. **Select Products**: Click on product cards to add to cart
2. **Adjust Quantities**: Use +/- buttons to modify amounts
3. **Review Order**: Check cart contents and total
4. **Checkout Process**:
   - Click "Checkout" button
   - Enter cash amount received
   - System calculates change automatically
   - Complete sale
5. **Print Receipt**: Generate customer receipt

#### 3. Cart Management
- **Add Items**: Click product cards
- **Remove Items**: Use trash icon
- **Clear Cart**: Clear all items button
- **Quantity Control**: Increment/decrement buttons

#### 4. Stock Awareness
- Products show stock levels
- Out-of-stock items are disabled
- Low stock warnings displayed

## 📱 Screenshots

### Admin Dashboard
![Admin Dashboard](docs/screenshots/admin-dashboard.png)
*Comprehensive analytics and quick access to all system features*

### POS Terminal
![POS Terminal](docs/screenshots/pos-terminal.png)
*Intuitive product selection with real-time cart management*

### Product Management
![Product Management](docs/screenshots/product-management.png)
*Easy product creation with image upload support*

### Transaction History
![Transaction History](docs/screenshots/transactions.png)
*Detailed transaction tracking with expandable item details*

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Errors
**Problem**: "Connection refused" or database errors
**Solution**:
1. Ensure XAMPP MySQL service is running
2. Check database credentials in `config/config.php`
3. Verify database `pos_db` exists
4. Import schema and seed data

#### Image Upload Issues
**Problem**: Product images not uploading
**Solution**:
1. Check `public/images/products/` directory exists
2. Verify directory permissions (755 or 777)
3. Ensure file size under 2MB
4. Use supported formats (JPG, PNG, GIF)

#### Login Problems
**Problem**: Cannot login with default credentials
**Solution**:
1. Verify seed data was imported correctly
2. Check users table in database
3. Reset password using forgot password feature
4. Clear browser cache and cookies

#### POS Layout Issues
**Problem**: Checkout button not visible
**Solution**:
1. Check browser zoom level (use 100%)
2. Clear browser cache
3. Try different browser
4. Check CSS file loading

#### Permission Errors
**Problem**: Access denied to admin features
**Solution**:
1. Verify user role in database
2. Check session data
3. Re-login to refresh permissions
4. Contact system administrator

### Performance Optimization

#### Database Optimization
- Add indexes on frequently queried columns
- Regular database maintenance and cleanup
- Optimize queries for large datasets
- Consider database connection pooling

#### File Management
- Implement image compression for uploads
- Regular cleanup of unused product images
- Consider CDN for static assets
- Optimize CSS and JavaScript files

#### Caching Strategies
- Implement PHP opcode caching
- Browser caching for static assets
- Session optimization
- Database query result caching

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create feature branch: `git checkout -b feature-name`
3. Make changes and test thoroughly
4. Commit changes: `git commit -m "Add feature"`
5. Push to branch: `git push origin feature-name`
6. Submit pull request

### Coding Standards
- Follow PSR-4 autoloading standards
- Use meaningful variable and function names
- Comment complex logic and algorithms
- Maintain consistent indentation (4 spaces)
- Validate all user inputs
- Use prepared statements for database queries

### Testing Guidelines
- Test all user roles and permissions
- Verify database operations
- Check responsive design on multiple devices
- Test file upload functionality
- Validate form submissions
- Test error handling scenarios

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the documentation
- Contact the development team

## 🔄 Version History

### v1.0.0 (Current)
- Initial release
- Basic POS functionality
- Admin dashboard
- User management
- Product management with images
- Transaction processing
- Authentication system
- Responsive design

### Planned Features (v1.1.0)
- Email notifications
- Advanced reporting
- Inventory alerts
- Multi-location support
- API documentation
- Mobile app integration

---

**Built with ❤️ for restaurant operations**

*This documentation is maintained and updated regularly. Last updated: December 2024*