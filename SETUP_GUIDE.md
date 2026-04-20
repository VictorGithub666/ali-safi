# Ali-Safi Platform - Setup Guide

## 📋 Prerequisites (Already Installed)
- PHP 8.3+
- Composer
- MySQL 8.0+
- Node.js & npm
- Git

---

## 🚀 Installation Steps

### Step 1: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Update .env file with your database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ali_safi
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Create Database
```bash
# Create the MySQL database
mysql -u root -p
```

In MySQL console:
```sql
CREATE DATABASE ali_safi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 4: Run Migrations
```bash
# Run all database migrations
php artisan migrate

# (Optional) Seed database with demo data
php artisan db:seed
```

### Step 5: Setup Storage Link
```bash
# Link storage directory for file uploads
php artisan storage:link
```

### Step 6: Google OAuth Setup (Optional - for later)
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials (Web Application)
5. Add authorized redirect URIs:
   - `http://localhost:8000/auth/google/callback`
   - Your production URL: `https://yourdomain.com/auth/google/callback`
6. Update `.env`:
```
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
```

### Step 7: Build Frontend Assets
```bash
# Build CSS and JS assets (development)
npm run dev

# OR for production
npm run build
```

### Step 8: Start Development Server
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite development server (auto-rebuild assets)
npm run dev
```

---

## 📱 Default Test Accounts (After Seeding)

### Customer
- Email: customer@example.com
- Password: password

### Vendor
- Email: vendor@example.com
- Password: password

### Rider
- Email: rider@example.com
- Password: password

### Admin
- Email: admin@example.com
- Password: password

---

## 🏗️ Project Structure

```
ali-safi/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Authentication controllers
│   │   │   ├── Admin/             # Admin panel controllers
│   │   │   ├── Customer/          # Customer app controllers
│   │   │   ├── Vendor/            # Vendor dashboard controllers
│   │   │   ├── Rider/             # Rider app controllers
│   │   │   └── ProfileController.php
│   │   ├── Middleware/            # Auth & role checks
│   │   └── Requests/              # Form validation
│   ├── Models/                    # Eloquent models
│   │   ├── User.php
│   │   ├── Order.php
│   │   ├── Product.php
│   │   ├── Vendor.php
│   │   ├── Rider.php
│   │   ├── Cart.php
│   │   └── ...
│   └── Notifications/             # Email notifications
├── database/
│   ├── migrations/                # Database schema
│   ├── seeders/                   # Demo data
│   └── factories/                 # Test data factories
├── resources/
│   ├── views/                     # Blade templates
│   │   ├── layouts/               # Base layouts
│   │   ├── auth/                  # Auth pages
│   │   ├── customer/              # Customer views
│   │   ├── vendor/                # Vendor views
│   │   ├── rider/                 # Rider views
│   │   ├── admin/                 # Admin views
│   │   └── components/            # Reusable components
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php                    # Web routes
│   ├── auth.php                   # Auth routes
│   └── api.php                    # API routes (optional)
├── config/                        # Configuration files
├── storage/                       # File uploads & logs
├── tests/                         # Unit & feature tests
├── public/                        # Static files & index.php
└── vendor/                        # Composer packages
```

---

## 🎨 Design System

### Color Scheme
- **Primary Green**: #05bb14 (logo, CTAs, success states)
- **Primary Blue**: #237bdd (secondary buttons, links)
- **Font**: Karl

### Bootstrap Integration
- Responsive grid system for mobile-first design
- Bootstrap components: buttons, cards, modals, forms
- Custom CSS variables for brand colors

---

## 📚 Database Schema Overview

### Tables
1. **users** - All user types (customer, vendor, rider, admin)
2. **vendors** - Vendor business details
3. **riders** - Rider vehicle & delivery info
4. **products** - Gas/water products with pricing
5. **categories** - Product categories
6. **vendor_products** - Join table with stock management
7. **orders** - Customer orders
8. **order_items** - Individual items in orders
9. **order_tracking** - Order status updates
10. **carts** - Shopping cart items
11. **ratings** - Customer ratings for vendors/riders
12. **transactions** - Payment records
13. **notifications** - System notifications
14. **settings** - Platform configuration

---

## 🔐 Authentication & Authorization

### User Types
- **Customer**: Place orders, view history, rate vendors
- **Vendor**: Manage products, accept orders, track earnings
- **Rider**: Accept deliveries, update status, track earnings
- **Admin**: Manage entire platform

### Middleware
- `verified` - Only verified email users
- `is_customer` - Customer role check
- `is_vendor` - Vendor role check
- `is_rider` - Rider role check
- `is_admin` - Admin role check

---

## 🔄 Core Workflows

### Customer Order Flow
1. Browse products
2. Add to cart
3. Checkout → System assigns nearest vendor & rider
4. Payment (cash/M-Pesa)
5. Real-time tracking
6. Delivery & rating

### Vendor Order Management
1. Receive order notification
2. Accept/reject order
3. Update product availability
4. Mark as prepared
5. Hand to rider
6. View earnings

### Rider Delivery Flow
1. Receive delivery request
2. Accept/decline job
3. Navigate to vendor (pickup)
4. Update status to "picked up"
5. Navigate to customer (delivery)
6. Confirm delivery
7. Receive delivery fee

### Admin Dashboard
1. Real-time order monitoring
2. Manual rider/vendor assignment
3. Pricing & commission management
4. Revenue tracking
5. User management

---

## 📞 Support & Documentation

- Laravel Docs: https://laravel.com/docs
- Bootstrap Docs: https://getbootstrap.com/docs
- Socialite Docs: https://laravel.com/docs/socialite

---

## ⚠️ Troubleshooting

### Migration Errors
```bash
# Rollback migrations
php artisan migrate:rollback

# Reset database
php artisan migrate:fresh
```

### Storage Permissions
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🎯 Next Steps

1. ✅ Complete migrations
2. ✅ Create Models & Relationships
3. ✅ Setup Authentication
4. ✅ Create Controllers & Routes
5. ✅ Build Views (Blade + Bootstrap)
6. ✅ Setup Image Upload
7. ⏳ Implement Real-time Features (WebSockets later)
8. ⏳ Mobile App Integration
9. ⏳ Payment Gateway (M-Pesa)
10. ⏳ Testing & Deployment
