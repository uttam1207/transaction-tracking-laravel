# Transaction Monitor - Enterprise Deployment Guide

## System Requirements

- PHP >= 8.2 (with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, MySQL, Tokenizer, XML, GD, ZIP)
- Composer >= 2.0
- MySQL >= 8.0 / MariaDB >= 10.4
- Node.js >= 18 (for asset compilation)
- Apache/Nginx web server

---

## Quick Start (XAMPP/Local)

### 1. Access the Application
- **URL:** `http://localhost/transaction-tracking/public`
- **Admin Login:** `admin@demo.com` / `Admin@123`
- **Super Admin:** `super@demo.com` / `Admin@123`
- **Employee:** `emp@demo.com` / `Admin@123`
- **Auditor:** `auditor@demo.com` / `Admin@123`

---

## Fresh Installation Steps

### Step 1: Clone / Extract
```bash
cd /path/to/your/webserver/htdocs
git clone <repo-url> transaction-tracking
cd transaction-tracking
```

### Step 2: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Step 3: Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database and mail settings:
```env
APP_NAME="Transaction Monitor"
APP_URL=http://localhost/transaction-tracking/public
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=transaction_monitor
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Database Setup
```bash
# Create database first
mysql -u root -p -e "CREATE DATABASE transaction_monitor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force
```

### Step 5: Storage & Permissions
```bash
php artisan storage:link
chmod -R 775 storage/ bootstrap/cache/
chown -R www-data:www-data storage/ bootstrap/cache/
```

### Step 6: Optimize
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Production Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/transaction-tracking/public

    <Directory /var/www/transaction-tracking/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/transaction-monitor-error.log
    CustomLog ${APACHE_LOG_DIR}/transaction-monitor-access.log combined
</VirtualHost>
```

## Production Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/transaction-tracking/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## Queue Worker (Production)

```bash
# Run queue worker
php artisan queue:work --sleep=3 --tries=3 --max-time=3600

# Supervisor configuration
[program:transaction-monitor-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/transaction-tracking/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/transaction-worker.log
```

## Scheduled Tasks (Crontab)

```bash
# Add to crontab: crontab -e
* * * * * cd /var/www/transaction-tracking && php artisan schedule:run >> /dev/null 2>&1
```

---

## REST API Documentation

### Base URL
```
http://yourdomain.com/api/v1
```

### Authentication
All protected endpoints require Bearer token:
```
Authorization: Bearer {your_token}
```

### Key Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/v1/auth/login | Authenticate and get token |
| POST | /api/v1/auth/logout | Invalidate token |
| GET | /api/v1/auth/me | Get current user |
| GET | /api/v1/transactions | List transactions |
| POST | /api/v1/transactions | Create transaction |
| GET | /api/v1/transactions/{id} | Get transaction details |
| GET | /api/v1/attendance | Get attendance records |
| POST | /api/v1/attendance/check-in | Employee check-in |
| POST | /api/v1/attendance/check-out | Employee check-out |
| GET | /api/v1/dashboard/admin-stats | Admin dashboard stats |
| GET | /api/v1/dashboard/employee-stats | Employee dashboard stats |
| GET | /api/v1/notifications | Get user notifications |
| GET | /api/health | API health check |

### Example API Call
```bash
# Login
curl -X POST http://localhost/transaction-tracking/public/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@demo.com","password":"Admin@123"}'

# List Transactions
curl -X GET http://localhost/transaction-tracking/public/api/v1/transactions \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## User Roles & Permissions

| Role | Dashboard | Transactions | Fraud Alerts | Employees | Reports | Settings |
|------|-----------|--------------|--------------|-----------|---------|----------|
| Super Admin | Full | Full | Full | Full | Full | Full |
| Admin | Full | Full | Full | CRUD | Full | Full |
| Manager | View | View | View | View | View | — |
| Employee | Own | — | — | — | — | — |
| Auditor | View | View | View | View | Full | — |
| Viewer | Limited | View | — | — | View | — |

---

## Module Overview

### 1. Authentication
- Login/Register/Forgot Password
- Two-Factor Authentication (TOTP)
- Email Verification
- OTP via Phone
- Rate limiting (5 attempts)
- Session tracking

### 2. Transaction Monitoring
- Real-time monitoring dashboard
- Fraud detection with risk scoring (0-100)
- Blacklist/Whitelist system
- Velocity checks
- Duplicate detection
- Country/IP geo-filtering
- Export to CSV/PDF

### 3. Employee Work Tracking
- Check-in/Check-out with IP logging
- Work hours calculation
- Break time tracking
- Overtime calculation
- Monthly attendance reports

### 4. Task Management
- Kanban board
- Priority levels (Low/Medium/High/Urgent)
- Time tracking with timers
- Task comments & attachments
- Approval/rejection workflow

### 5. Fraud Detection Rules
- 5 built-in fraud rules (configurable)
- High amount detection
- Velocity checks
- Duplicate transaction detection
- Geographic restrictions
- Blacklist enforcement

---

## Security Features

- CSRF protection on all forms
- Rate limiting on login (5 attempts/5 min)
- SQL injection protection via Eloquent ORM
- XSS prevention via Blade auto-escaping
- Password hashing (bcrypt, cost=12)
- Sanctum API token authentication
- Role-based access control (Spatie)
- Activity logging on all models
- Audit trail for all CRUD operations
- Session management with device tracking
- IP tracking on all logins

---

## Environment Variables Reference

```env
# Application
APP_NAME="Transaction Monitor"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=transaction_monitor
DB_USERNAME=db_user
DB_PASSWORD=strong_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@yourdomain.com

# Queue
QUEUE_CONNECTION=database

# Broadcasting (Real-time)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001

# reCAPTCHA
GOOGLE_RECAPTCHA_KEY=your_site_key
GOOGLE_RECAPTCHA_SECRET=your_secret_key
```
