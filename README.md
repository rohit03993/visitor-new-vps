# Visitor Management System (VMS) - CRM

A comprehensive Laravel-based Visitor Management System designed for educational institutions and organizations to manage visitor interactions, employee assignments, and branch-based permissions.

## 🚀 Features

### Core Functionality
- **Visitor Registration**: Complete visitor information management
- **Interaction Tracking**: Record and track all visitor interactions
- **Employee Assignment**: Assign visitors to specific employees
- **Branch Management**: Multi-branch support with location-based access
- **Role-Based Access Control**: Admin, Frontdesk, and Employee roles
- **Mobile-Optimized**: Responsive design for mobile and tablet devices

### Advanced Features
- **User Deactivation/Reactivation**: Safe user management without data loss
- **Branch Permissions**: Granular control over remarks and Excel download permissions
- **Print Functionality**: Advanced print options with pagination support
- **Search & Analytics**: Comprehensive search and reporting capabilities
- **Address Management**: Dynamic address suggestions and management
- **Remark System**: Multi-level remark tracking with permission controls

### Security Features
- **CSRF Protection**: All forms protected against CSRF attacks
- **Role-Based Middleware**: Secure route protection
- **User Authentication**: Secure login with deactivation checks
- **Data Validation**: Comprehensive input validation

## 🛠️ Technology Stack

- **Backend**: Laravel 11
- **Frontend**: Bootstrap 5, JavaScript, AJAX
- **Database**: MySQL
- **Authentication**: Laravel Auth
- **Caching**: Laravel Cache
- **Icons**: Font Awesome

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- MySQL 5.7 or higher
- Node.js & NPM (for asset compilation)

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd visitor-management-system
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   - Update `.env` file with your database credentials
   - Create database in MySQL

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Compile assets**
   ```bash
   npm run build
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

## 👥 Default Users

After running seeders, you'll have these default users:

### Admin User
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: Admin (Full access)

### Frontdesk Users
- **Username**: `fd_amit_patel`
- **Password**: `password123`
- **Role**: Frontdesk

### Employee Users
- **Username**: `emp_arjun_singh`
- **Password**: `password123`
- **Role**: Employee

## 🏗️ Project Structure

```
visitor-management-system/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/              # Eloquent models
│   └── Helpers/             # Helper classes
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── views/              # Blade templates
│   └── css/                # Stylesheets
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
└── public/                 # Public assets
```

## 🔧 Configuration

### Branch Permissions
- Configure branch-specific permissions in Admin → Manage Users
- Set `can_view_remarks` and `can_download_excel` per branch

### User Management
- Deactivate users instead of deleting (preserves all data)
- Reactivate users when needed
- Users automatically excluded from meeting dropdowns when deactivated

### Mobile Optimization
- Card-based layout for mobile devices
- Responsive tables with mobile alternatives
- Touch-friendly interface

## 📱 Mobile Features

- **Card Layout**: Mobile-optimized card views
- **Touch Navigation**: Easy mobile navigation
- **Responsive Forms**: Mobile-friendly form inputs
- **Print Options**: Mobile-optimized print functionality

## 🔒 Security

- **User Deactivation**: Prevents login without data loss
- **Branch Permissions**: Granular access control
- **CSRF Protection**: All forms protected
- **Input Validation**: Comprehensive validation rules

## 🚀 Deployment

### Production Deployment
1. Set up production environment variables
2. Run `php artisan config:cache`
3. Run `php artisan route:cache`
4. Run `php artisan view:cache`
5. Set up web server (Apache/Nginx)
6. Configure SSL certificate

### Server Requirements
- PHP 8.1+
- MySQL 5.7+
- Web server (Apache/Nginx)
- SSL certificate (recommended)

## 📊 Features Overview

### Admin Dashboard
- Complete system overview
- User management with deactivation/reactivation
- Branch and address management
- Analytics and reporting

### Frontdesk Dashboard
- Visitor registration
- Interaction management
- Search functionality
- Print options

### Employee Dashboard
- Assigned interactions
- Remark management
- Visitor history

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software. All rights reserved.

## 📞 Support

For support and questions, please contact the development team.

---

**Version**: 1.0.0  
**Last Updated**: January 2025  
**Laravel Version**: 11.x