# Technology Stack

## Backend
- **Language**: PHP 7.4+
- **Database**: MySQL 8.0 (via XAMPP)
- **Server**: Apache (XAMPP)
- **Session Management**: PHP native sessions

## Frontend
- **CSS Framework**: Bootstrap 5.3.3
- **Custom CSS**: Modern design system with CSS variables
- **JavaScript**: Vanilla JS (no framework)
- **Font**: Inter (Google Fonts)

## Architecture Patterns
- **MVC-inspired**: Separation of concerns with app/, views/, public/
- **Database Access**: PDO with prepared statements
- **Authentication**: Custom Auth class with session management
- **Configuration**: Centralized config file

## Database Connection
- Singleton pattern via `Database::getConnection()`
- PDO with error mode set to exceptions
- UTF-8 character set (utf8mb4)

## Common Commands

### Database Setup
```bash
# Access phpMyAdmin
http://localhost/phpmyadmin

# Import schema
# 1. Create database 'pos_db'
# 2. Import database/schema.sql
# 3. Import database/seed.sql
```

### Development Server
```bash
# XAMPP Apache server
# Start via XAMPP Control Panel
# Access: http://localhost/Point-of-sales/public/
```

### Testing Credentials
- Admin: `admin` / `admin123`
- Cashier: `cashier` / `cashier123`

## Security Practices
- Password hashing with `password_hash()` and `password_verify()`
- Prepared statements for SQL injection prevention
- `htmlspecialchars()` for XSS protection
- Session-based authentication
- Role-based access control
