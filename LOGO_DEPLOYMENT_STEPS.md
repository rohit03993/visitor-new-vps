# 🚀 Logo Feature Deployment Steps (For All Sites)

## Apply these steps to:
- ✅ motionagra.com (DONE)
- ⏳ motion.taskbook.co.in (TODO)
- ⏳ horizon.taskbook.co.in (TODO)

---

## 🔑 **IMPORTANT: Each Site Uses Different User!**

- **motionagra.com** → User: `motionagra`
- **motion.taskbook.co.in** → User: `taskbook-motion`  
- **horizon.taskbook.co.in** → User: `taskbook-horizon`

**Always use the correct user in chown commands!**

---

## 📋 Step-by-Step Commands:

### **1. Navigate to Project Directory**
```bash
# For motionagra.com:
cd /home/motionagra/htdocs/motionagra.com

# For motion.taskbook.co.in:
# cd /home/taskbook-motion/htdocs/motion.taskbook.co.in

# For horizon.taskbook.co.in:
# cd /home/taskbook-horizon/htdocs/horizon.taskbook.co.in
```

### **2. Pull Latest Code**
```bash
git pull origin master
```

### **3. Run Migration (Creates settings table)**
```bash
php artisan migrate
```

### **4. Clear All Caches**
```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

### **5. Fix Logo Directory Permissions**
```bash
# Create directory
mkdir -p public/images/logos

# Set ownership based on site:
# For motionagra.com:
chown -R motionagra:motionagra public/images

# For motion.taskbook.co.in:
# chown -R taskbook-motion:taskbook-motion public/images

# For horizon.taskbook.co.in:
# chown -R taskbook-horizon:taskbook-horizon public/images

# Set permissions (all sites)
chmod -R 775 public/images
```

### **6. Fix Storage Permissions (For Uploads/Attachments)**
```bash
# Fix symlink ownership (use site-specific user)
# For motionagra.com:
chown -h motionagra:motionagra public/storage

# For motion.taskbook.co.in:
# chown -h taskbook-motion:taskbook-motion public/storage

# For horizon.taskbook.co.in:
# chown -h taskbook-horizon:taskbook-horizon public/storage

# Fix directory permissions (all sites)
chmod -R 755 storage/app/public/uploads

# Fix file permissions (all sites)
find storage/app/public/uploads -type f -exec chmod 644 {} \;
```

### **7. Insert Default Logo Setting (if needed)**
```bash
php artisan tinker
```
Then run:
```php
DB::table('settings')->insert(['key' => 'app_logo', 'value' => 'images/logos/default-logo.svg', 'created_at' => now(), 'updated_at' => now()]);
exit
```

### **8. Test in Browser**
- Go to login page
- Should see default TaskBook logo
- Login as admin
- Go to /admin/settings
- Upload company logo

---

## 🎯 Expected Results:

✅ Login page shows logo in rounded white container
✅ Sidebar shows logo at top
✅ "Powered by TaskBook" at bottom of login page
✅ "Powered by TaskBook" at bottom of sidebar
✅ Admin can upload custom logo from settings page

---

## ⚠️ Common Issues & Fixes:

### Issue: Route [admin.settings] not defined
**Fix:**
```bash
php artisan route:clear
php artisan cache:clear
```

### Issue: Unable to write to logos directory
**Fix:** Use the CORRECT user for each site (not www-data)
```bash
# For motionagra.com:
chown -R motionagra:motionagra public/images
chmod -R 775 public/images

# For motion.taskbook.co.in:
# chown -R taskbook-motion:taskbook-motion public/images
# chmod -R 775 public/images

# For horizon.taskbook.co.in:
# chown -R taskbook-horizon:taskbook-horizon public/images
# chmod -R 775 public/images
```

**IMPORTANT:** Each site runs under its own user - use the correct one!

### Issue: Logo uploaded but not showing
**Fix:**
```bash
php artisan tinker
```
Then:
```php
DB::table('settings')->where('key', 'app_logo')->update(['value' => 'images/logos/YOUR-UPLOADED-FILE.png', 'updated_at' => now()])
exit
```

### Issue: 403 Forbidden when viewing uploaded files/attachments
**Fix:**
```bash
chown -h motionagra:motionagra public/storage
chmod -R 755 storage/app/public/uploads
find storage/app/public/uploads -type f -exec chmod 644 {} \;
```
(Replace `motionagra` with the appropriate user for each site)

---

## 📦 Files Added/Modified:

**New Files:**
- app/Models/Setting.php
- resources/views/admin/settings.blade.php
- database/migrations/2025_09_30_000000_create_settings_table.php

**Modified Files:**
- resources/views/layouts/app.blade.php
- resources/views/auth/login.blade.php
- app/Http/Controllers/AdminController.php
- routes/web.php

---

## ✅ Quick Deployment Checklist:

- [ ] Navigate to project directory
- [ ] git pull origin master
- [ ] php artisan migrate
- [ ] php artisan cache:clear
- [ ] php artisan route:clear
- [ ] Fix permissions on public/images/logos
- [ ] Test login page
- [ ] Test admin settings page
- [ ] Upload company logo
- [ ] Verify logo appears everywhere

---

## 🎨 Features Deployed:

1. Admin Settings Page (/admin/settings)
2. Logo Upload Functionality
3. Rounded Logo Containers (Login + Sidebar)
4. "Powered by TaskBook" Footer
5. Auto-fit Logo Styling (works with any image size/format)
6. Safe Implementation (works without migration initially)

---

**Total Time: ~5 minutes per site** ⚡
