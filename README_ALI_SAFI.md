# Ali-Safi Platform - Gas & Water Delivery Marketplace

A modern Laravel-based marketplace platform connecting customers, vendors (gas shops and water refill stations), and riders (delivery drivers) for gas and water delivery services.

## 📱 Features

### Customer Features
- Browse and search gas/water products
- Shopping cart functionality
- Secure checkout with multiple payment methods (cash, M-Pesa)
- Real-time order tracking with live rider location
- Order history and status notifications
- Rating and review system
- Profile management with picture upload
- Google OAuth signup for quick registration

### Vendor Features
- Receive and manage orders in real-time
- Update product availability and stock levels
- View earnings and revenue reports
- Online/offline status management
- Performance analytics and ratings
- Order history and customer feedback

### Rider Features
- Receive available delivery requests
- Accept/decline delivery jobs
- Real-time GPS location tracking
- Navigation to vendor and customer locations
- Delivery status updates (picked up, delivered)
- Earnings tracking and history
- Performance ratings

### Admin Panel
- Real-time order monitoring and management
- Manual or automatic rider assignment
- Pricing and commission configuration
- Revenue and performance analytics
- Vendor and rider verification and management
- Platform settings and configuration

---

## 🛠 Tech Stack

- **Framework**: Laravel 13
- **Frontend**: Blade Templates + Bootstrap 5
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Breeze + Socialite (Google OAuth)
- **Image Processing**: Intervention Image
- **Asset Building**: Vite
- **Real-time**: Pusher (optional, for future implementation)

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.3+
- Composer
- MySQL Server 8.0+
- Node.js & npm
- Git

---

## 🚀 Installation & Setup

### Step 1: Clone and Setup Repository
```bash
cd /path/to/project
git clone <repository-url>
cd ali-safi
```

### Step 2: Environment Configuration
```bash
# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 3: Update .env File
Edit `.env` and update these values:

```env
APP_NAME="Ali-Safi"
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ali_safi
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

# Mail Configuration (optional)
MAIL_MAILER=log

# Google OAuth (optional for signup)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Step 4: Create Database
```bash
# Using MySQL CLI
mysql -u root -p
```

In MySQL console:
```sql
CREATE DATABASE ali_safi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 5: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 6: Database Setup
```bash
# Run migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed
```

### Step 7: Create Storage Link
```bash
php artisan storage:link
```

### Step 8: Build Frontend Assets
```bash
# For development with hot reload
npm run dev

# For production build
npm run build
```

### Step 9: Start Development Server
Open two terminals:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite Asset Server:**
```bash
npm run dev
```

Access the application at: **http://localhost:8000**

---

## 📚 Test Accounts

After seeding, use these credentials to test:

### Admin
- Email: `admin@example.com`
- Password: `password`

### Customer (5 accounts available)
- Email: `customer1@example.com`
- Password: `password`

### Vendor (3 accounts available)
- Email: `vendor1@example.com`
- Password: `password`

### Rider (5 accounts available)
- Email: `rider1@example.com`
- Password: `password`

---

## 🗂 Project Structure

```
ali-safi/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Authentication controllers
│   │   │   ├── Admin/             # Admin panel controllers
│   │   │   ├── Customer/          # Customer app controllers
│   │   │   ├── Vendor/            # Vendor dashboard controllers
│   │   │   └── Rider/             # Rider app controllers
│   │   └── Middleware/            # Custom middleware
│   ├── Models/                    # Eloquent models
│   ├── Notifications/             # Email notifications
│   └── Services/                  # Business logic services
│
├── database/
│   ├── migrations/                # Database schema
│   ├── seeders/                   # Demo & test data
│   └── factories/                 # Model factories
│
├── resources/
│   ├── views/
│   │   ├── layouts/               # Master layouts
│   │   ├── auth/                  # Authentication pages
│   │   ├── customer/              # Customer pages
│   │   ├── vendor/                # Vendor pages
│   │   ├── rider/                 # Rider pages
│   │   ├── admin/                 # Admin pages
│   │   └── components/            # Reusable components
│   ├── css/                       # Stylesheets
│   └── js/                        # JavaScript
│
├── routes/
│   ├── web.php                    # Web routes
│   ├── auth.php                   # Authentication routes
│   └── api.php                    # API routes (optional)
│
├── config/
│   ├── app.php                    # App configuration
│   ├── database.php               # Database configuration
│   └── services.php               # Third-party services
│
├── public/                        # Static files & index.php
├── storage/                       # File uploads & logs
├── tests/                         # Test files
└── vendor/                        # Composer packages
```

---

## 🏗 Database Schema

### Core Tables
1. **users** - All user types with authentication
2. **vendors** - Vendor business details
3. **riders** - Rider vehicle and delivery info
4. **products** - Gas/water products with pricing
5. **categories** - Product categories
6. **vendor_products** - Vendor product inventory
7. **orders** - Customer orders
8. **order_items** - Order line items
9. **order_tracking** - Order status history
10. **carts** - Shopping cart items
11. **ratings** - Customer ratings
12. **transactions** - Payment records
13. **notifications** - System notifications
14. **settings** - Platform configuration

---

##🔐 Authentication & Authorization

### User Types
- **Customer**: Place orders, view history, rate vendors
- **Vendor**: Manage products and orders
- **Rider**: Manage deliveries
- **Admin**: Platform administration

### Middleware
```php
'auth'           // User must be authenticated
'verified'       // Email must be verified
'user.type:role' // Role-based access control
```

---

## 🎨 Design System

### Brand Colors
- **Primary Green**: `#05bb14`
- **Primary Blue**: `#237bdd`

### Font
- **Font Family**: Karl

### Responsive Design
- Bootstrap 5 for responsive grid
- Mobile-first approach
- Bootstrap components with custom theming

---

## 🔄 Core Workflows

### Order Placement Flow
1. Customer browses products
2. Selects items and adds to cart
3. Proceeds to checkout
4. Enters delivery address
5. Selects payment method
6. System automatically:
   - Assigns nearest vendor
   - Assigns nearest available rider
   - Creates order and items
   - Sends notifications
7. Order enters "pending" status
8. Vendor receives notification

### Order Fulfillment Flow
1. Vendor receives order notification
2. Vendor accepts/confirms order
3. Vendor prepares items
4. Marks order as "ready_for_pickup"
5. Rider accepts delivery
6. Rider picks up from vendor
7. Rider delivers to customer
8. Orders marked as "delivered"
9. Platform takes commission

---

## 📦 Installation Checklist

- [ ] Repository cloned
- [ ] `.env` configured with database credentials
- [ ] Database created
- [ ] `php artisan migrate` completed
- [ ] `php artisan db:seed` completed
- [ ] `php artisan storage:link` executed
- [ ] `npm install` completed
- [ ] `npm run dev` running
- [ ] `php artisan serve` running
- [ ] Visited `http://localhost:8000`
- [ ] Logged in with test account
- [ ] Tested all user roles

---

## 🚀 Next Steps (Not Implemented Yet)

1. **Real-time Features**
   - Live order updates using Pusher/WebSockets
   - Live rider tracking
   - Real-time notifications

2. **Payment Integration**
   - M-Pesa integration
   - Card payment processing
   - Wallet system

3. **Advanced Features**
   - ML-based smart matching algorithm
   - Surge pricing
   - Rating system reviews
   - Customer loyalty program

4. **Mobile Apps**
   - Native iOS app
   - Native Android app
   - API for mobile apps

5. **DevOps**
   - CI/CD pipeline setup
   - Docker containerization
   - Production deployment

---

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Verify MySQL is running
mysql -u root -p

# Check .env database credentials
# Ensure database exists: CREATE DATABASE ali_safi;
```

### Storage Permission Issues
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # For Linux/Mac
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Reset Database
```bash
# Drop all tables and re-run migrations
php artisan migrate:fresh --seed
```

---

## 📞 Support

For issues and questions:
1. Check the [Laravel Documentation](https://laravel.com/docs)
2. Check the [Bootstrap Documentation](https://getbootstrap.com/docs)
3. Review the code comments and this README

---

## 📄 License

MIT License

---

## 🎯 Project Status

**Current Phase**: MVP (Minimum Viable Product)

### Completed
✅ Database schema and migrations
✅ User authentication (email & Google OAuth)
✅ Role-based access control
✅ Model relationships
✅ Controllers and routes
✅ Order management flow
✅ Cart system
✅ Profile management

### In Progress
🔄 View templates (Blade + Bootstrap)
🔄 Image upload functionality
🔄 Admin dashboard

### Future
⏳ Real-time features (WebSockets)
⏳ Payment gateway integration
⏳ Mobile apps
⏳ Advanced analytics
⏳ AI-based matching algorithm

---

## 📝 Notes

- All prices are in KES (Kenyan Shillings)
- Default delivery fee: 50 KES
- Platform commission: 5%
- Rider delivery fee: Negotiable (part of order total)
- Test data generates 5 customers, 3 vendors, 5 riders

---

## 🤝 Contributing

This is a private project for Ali-Safi. For contributions, please contact the project owner.

---

**Built with ❤️ for Ali-Safi Platform**
