# 🚀 Quick Deploy Commands - Copy & Paste for Each Site

---

## 🌐 **For motionagra.com** ✅ DONE

```bash
cd /home/motionagra/htdocs/motionagra.com
git pull origin master
php artisan migrate
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
mkdir -p public/images/logos
chown -R motionagra:motionagra public/images
chmod -R 775 public/images
chown -h motionagra:motionagra public/storage
chmod -R 755 storage/app/public/uploads
find storage/app/public/uploads -type f -exec chmod 644 {} \;
```

---

## 🌐 **For motion.taskbook.co.in** ⏳ TODO

```bash
cd /home/taskbook-motion/htdocs/motion.taskbook.co.in
git pull origin master
php artisan migrate
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
mkdir -p public/images/logos
chown -R taskbook-motion:taskbook-motion public/images
chmod -R 775 public/images
chown -h taskbook-motion:taskbook-motion public/storage
chmod -R 755 storage/app/public/uploads
find storage/app/public/uploads -type f -exec chmod 644 {} \;
```

---

## 🌐 **For horizon.taskbook.co.in** ⏳ TODO

```bash
cd /home/taskbook-horizon/htdocs/horizon.taskbook.co.in
git pull origin master
php artisan migrate
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan view:clear
mkdir -p public/images/logos
chown -R taskbook-horizon:taskbook-horizon public/images
chmod -R 775 public/images
chown -h taskbook-horizon:taskbook-horizon public/storage
chmod -R 755 storage/app/public/uploads
find storage/app/public/uploads -type f -exec chmod 644 {} \;
```

---

## 🎯 **After Running Commands:**

### For Each Site:
1. Visit: `https://[SITE-URL]/admin/settings`
2. Upload company-specific logo
3. Test login page
4. Test sidebar

---

## 📝 **Post-Deployment Checklist:**

- [ ] Logo upload works from admin panel
- [ ] Logo shows on login page (rounded white container)
- [ ] Logo shows in sidebar (rounded white container)
- [ ] "Powered by TaskBook" shows on login page
- [ ] "Powered by TaskBook" shows in sidebar
- [ ] Attachment/file uploads work (no 403 errors)
- [ ] All CRM features working

---

## ⚡ **Super Quick Deploy (One Command Per Site):**

### motionagra.com:
```bash
cd /home/motionagra/htdocs/motionagra.com && git pull origin master && php artisan migrate && php artisan cache:clear && php artisan route:clear && chown -R motionagra:motionagra public/images && chmod -R 775 public/images && chown -h motionagra:motionagra public/storage
```

### motion.taskbook.co.in:
```bash
cd /home/taskbook-motion/htdocs/motion.taskbook.co.in && git pull origin master && php artisan migrate && php artisan cache:clear && php artisan route:clear && chown -R taskbook-motion:taskbook-motion public/images && chmod -R 775 public/images && chown -h taskbook-motion:taskbook-motion public/storage
```

### horizon.taskbook.co.in:
```bash
cd /home/taskbook-horizon/htdocs/horizon.taskbook.co.in && git pull origin master && php artisan migrate && php artisan cache:clear && php artisan route:clear && chown -R taskbook-horizon:taskbook-horizon public/images && chmod -R 775 public/images && chown -h taskbook-horizon:taskbook-horizon public/storage
```

---

## 🔑 **Key Lesson Learned:**

**Each site runs under its OWN user:**
- motionagra.com → `motionagra`
- motion.taskbook.co.in → `taskbook-motion`
- horizon.taskbook.co.in → `taskbook-horizon`

**Always use the site-specific user in permission commands!**

---

## 💾 **Save This File!**

Keep this file handy - you can copy-paste these commands anytime you deploy updates! 📋✨
