# 🎨 Logo Upload Feature - Setup Instructions

## ✅ What's Been Done

1. **Database Setup**: Created `settings` table to store logo and app name
2. **Model Created**: `Setting` model with helper methods
3. **Controller Methods**: Added to `AdminController` for logo upload
4. **Admin Page**: Created settings page at `/admin/settings`
5. **Routes**: Added routes for settings management
6. **Views Updated**: 
   - ✅ Sidebar shows uploaded logo
   - ✅ Login page shows uploaded logo

## 🚀 Setup Steps (Run These Commands)

### Step 1: Run Migration
```bash
php artisan migrate
```

This will create the `settings` table with default values.

### Step 2: Test the Feature

1. **Login as Admin**
2. **Go to**: http://localhost:8000/admin/settings
3. **Upload Your Logo**: Click "Choose File" and select your company logo
4. **See Results**: Logo will appear on:
   - Login page
   - Sidebar (all pages)

## 📁 File Structure

```
database/migrations/
└── 2025_09_30_000000_create_settings_table.php   ✅ Created

app/Models/
└── Setting.php                                    ✅ Created

app/Http/Controllers/
└── AdminController.php                            ✅ Updated (added 3 methods)

resources/views/
├── layouts/app.blade.php                         ✅ Updated (uses Setting::logo())
├── auth/login.blade.php                          ✅ Updated (uses Setting::logo())
└── admin/settings.blade.php                      ✅ Created (upload page)

routes/
└── web.php                                        ✅ Updated (added routes)

public/images/logos/
├── default-logo.svg                              ✅ Default logo
├── motion-logo.svg                               ✅ Sample (will be replaced)
└── horizon-logo.svg                              ✅ Sample (will be replaced)
```

## 🎯 How It Works

### For Admin:
1. Go to **Admin → App Settings**
2. See current logo
3. Upload new logo (PNG, JPG, or SVG)
4. Logo automatically appears everywhere

### For Users:
- Logo appears on login page
- Logo appears in sidebar
- No manual configuration needed

## 🔧 Features

✅ **Simple**: Admin uploads logo from admin panel  
✅ **Automatic**: Logo updates everywhere instantly  
✅ **Cached**: Fast loading with Laravel cache  
✅ **Flexible**: Supports PNG, JPG, and SVG formats  
✅ **Preview**: See logo before uploading  

## 📝 Default Settings

After migration, default values are:
- **Logo**: `images/logos/default-logo.svg`
- **App Name**: `TaskBook`

## 🧪 Testing Locally

1. Run: `php artisan migrate`
2. Visit: http://localhost:8000/login (see logo)
3. Login as admin
4. Visit: http://localhost:8000/admin/settings
5. Upload your logo
6. Refresh login page - see your logo!

## 🌐 Production Deployment

When deploying to server:
1. Run migrations: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Upload logo via admin panel
4. Done!

## ❓ Troubleshooting

**Logo not showing?**
- Clear cache: `php artisan cache:clear`
- Check file permissions on `public/images/logos/`
- Verify migration ran: `php artisan migrate:status`

**Can't upload?**
- Check `storage` directory permissions
- Verify max upload size in `php.ini`
- Check server error logs

## 📸 Screenshots Reference

Your two screenshots show:
1. **Sidebar** - Logo at top (✅ Done)
2. **Login Page** - Logo in center (✅ Done)

Both now use the uploaded logo!
