# Product Overview

## Restaurant Point of Sale (POS) System

A web-based point of sale system designed for restaurant operations with role-based access control.

### Core Purpose
- Enable cashiers to process sales transactions quickly
- Provide administrators with business analytics and inventory management
- Track sales, products, categories, and user activities

### Key Features
- **Cashier Dashboard**: Full-screen POS terminal for fast transaction processing
- **Admin Dashboard**: Analytics, inventory management, and system configuration
- **Role-Based Access**: Separate interfaces for Admin and Cashier roles
- **Inventory Management**: Real-time stock tracking and low-stock alerts
- **Sales Analytics**: Daily and monthly sales reporting

### User Roles
1. **Admin**: Full system access including dashboard, products, categories, users, and POS
2. **Cashier**: Limited to POS terminal for processing sales only

### Authentication Model
- No public signup - all accounts are provisioned by administrators
- No self-service password reset - employees must contact admin for password resets
- Session-based authentication with role-based routing
