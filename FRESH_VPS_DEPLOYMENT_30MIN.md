# 🚀 FRESH VPS DEPLOYMENT - 30 MINUTE GUIDE
## Deploy motion.taskbook.co.in to New VPS

---

## ⏱️ TIME BREAKDOWN (30 Minutes Total)

- **5 min:** Server preparation & Git clone
- **10 min:** Laravel setup & dependencies
- **5 min:** Database & migrations
- **5 min:** Permissions & storage
- **5 min:** Web server configuration
- **Buffer:** Testing & fixes

---

## ✅ PRE-DEPLOYMENT CHECKLIST

Before starting, ensure you have:
- [ ] VPS server access (SSH credentials)
- [ ] Domain pointing to VPS IP (motion.taskbook.co.in)
- [ ] Database credentials ready
- [ ] GitHub access configured on server

---

## 🎯 PHASE 1: SERVER PREPARATION (5 min)

### Step 1: SSH into New VPS
```bash
ssh root@YOUR_VPS_IP
```

### Step 2: Update System (if needed)
```bash
apt update && apt upgrade -y
```

### Step 3: Install Required Software
```bash
# PHP 8.1+ and extensions
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-redis

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Git (if not installed)
apt install -y git

# Nginx (or Apache)
apt install -y nginx

# MySQL (or use external database)
apt install -y mysql-server
```

### Step 4: Create Project Directory
```bash
mkdir -p /var/www/motion.taskbook.co.in
cd /var/www/motion.taskbook.co.in
```

---

## 🎯 PHASE 2: CLONE & SETUP LARAVEL (10 min)

### Step 5: Clone Repository
```bash
git clone https://github.com/rohit03993/visitor-new-vps.git .
```

### Step 6: Install Composer Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### Step 7: Create .env File
```bash
cp .env.example .env
nano .env
```

**Required .env Settings:**
```env
APP_NAME="Motion TaskBook"
APP_ENV=production
APP_KEY=  # Will generate in next step
APP_DEBUG=false
APP_URL=https://motion.taskbook.co.in

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motion_taskbook_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Save and exit: `Ctrl+X`, then `Y`, then `Enter`

### Step 8: Generate Application Key
```bash
php artisan key:generate
```

---

## 🎯 PHASE 3: DATABASE SETUP (5 min)

### Step 9: Create Database
```bash
mysql -u root -p
```

Then in MySQL:
```sql
CREATE DATABASE motion_taskbook_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'motion_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON motion_taskbook_db.* TO 'motion_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 10: Run Migrations
```bash
php artisan migrate --force
```

### Step 11: Seed Initial Data (Optional)
```bash
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=TagSeeder
```

---

## 🎯 PHASE 4: PERMISSIONS & STORAGE (5 min)

### Step 12: Set Ownership
```bash
# Create a user for the site (if not exists)
useradd -m taskbook-motion

# Set ownership
chown -R taskbook-motion:taskbook-motion /var/www/motion.taskbook.co.in
```

### Step 13: Set Directory Permissions
```bash
cd /var/www/motion.taskbook.co.in

# Storage and cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Logo upload directory
mkdir -p public/images/logos
chown -R taskbook-motion:taskbook-motion public/images
chmod -R 775 public/images

# Create storage link
php artisan storage:link

# Fix storage permissions
chown -h taskbook-motion:taskbook-motion public/storage
chmod -R 755 storage/app/public
```

### Step 14: Insert Default Logo Setting
```bash
php artisan tinker --execute="DB::table('settings')->insert(['key' => 'app_logo', 'value' => 'images/logos/default-logo.svg', 'created_at' => now(), 'updated_at' => now()]);"
```

---

## 🎯 PHASE 5: WEB SERVER SETUP (5 min)

### Step 15: Create Nginx Configuration
```bash
nano /etc/nginx/sites-available/motion.taskbook.co.in
```

**Paste this configuration:**
```nginx
server {
    listen 80;
    server_name motion.taskbook.co.in;
    root /var/www/motion.taskbook.co.in/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Save: `Ctrl+X`, `Y`, `Enter`

### Step 16: Enable Site
```bash
ln -s /etc/nginx/sites-available/motion.taskbook.co.in /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### Step 17: Install SSL Certificate (Certbot)
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d motion.taskbook.co.in
```

---

## 🎯 PHASE 6: FINAL STEPS (5 min)

### Step 18: Optimize Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 19: Set Up Queue Worker (Optional)
```bash
nano /etc/supervisor/conf.d/motion-taskbook-worker.conf
```

Paste:
```ini
[program:motion-taskbook-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/motion.taskbook.co.in/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=taskbook-motion
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/motion.taskbook.co.in/storage/logs/worker.log
stopwaitsecs=3600
```

Then:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start motion-taskbook-worker:*
```

### Step 20: Create First Admin User
```bash
php artisan tinker
```

Then:
```php
\App\Models\VmsUser::create([
    'username' => 'admin',
    'password' => bcrypt('your_secure_password'),
    'name' => 'Admin',
    'email' => 'admin@motion.com',
    'role' => 'admin',
    'is_active' => true,
    'branch_id' => null
]);
exit
```

---

## 🧪 TESTING CHECKLIST

- [ ] Visit https://motion.taskbook.co.in
- [ ] Login page loads with logo
- [ ] Can login with admin credentials
- [ ] Dashboard loads
- [ ] Can add visitor
- [ ] Can add interaction
- [ ] Can upload files
- [ ] Can view remarks
- [ ] Admin settings page works
- [ ] Can upload company logo

---

## 📋 QUICK DEPLOYMENT SCRIPT (Copy-Paste)

Save this as `deploy-motion.sh`:

```bash
#!/bin/bash

echo "🚀 Deploying Motion TaskBook CRM..."

# Navigate to directory
cd /var/www/motion.taskbook.co.in

# Clone repository
git clone https://github.com/rohit03993/visitor-new-vps.git .

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
# MANUALLY EDIT .env NOW

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed data
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=TagSeeder

# Set permissions
chown -R taskbook-motion:taskbook-motion /var/www/motion.taskbook.co.in
chmod -R 775 storage bootstrap/cache
mkdir -p public/images/logos
chown -R taskbook-motion:taskbook-motion public/images
chmod -R 775 public/images

# Create storage link
php artisan storage:link
chown -h taskbook-motion:taskbook-motion public/storage

# Insert default logo
php artisan tinker --execute="DB::table('settings')->insert(['key' => 'app_logo', 'value' => 'images/logos/default-logo.svg', 'created_at' => now(), 'updated_at' => now()]);"

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment Complete!"
echo "Next: Configure Nginx and create admin user"
```

---

## ⚠️ CRITICAL FILES TO CHECK IN GIT

Let me verify what's in your Git:

```bash
# On your LOCAL machine, run:
git ls-files | grep -E "(composer.json|.env.example|artisan|public/index.php)"
```

---

## 🎯 MISSING FILES CHECK

Files that MUST be in Git:
- ✅ composer.json
- ✅ composer.lock
- ✅ .env.example
- ✅ artisan
- ✅ public/index.php
- ✅ All app/ files
- ✅ All routes/ files
- ✅ All resources/ files
- ✅ All database/migrations/ files

---

**Let's verify your Git has everything. Run this on your LOCAL machine:**

```bash
git status
```

**Then run:**

```bash
git ls-files | wc -l
```

This shows how many files are tracked in Git.

**Share the output so I can confirm everything is ready!** 🚀
