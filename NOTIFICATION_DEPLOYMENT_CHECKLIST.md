# 🔔 Push Notification System - Deployment Checklist

## ✅ **LOCAL TESTING COMPLETED**
- [x] Desktop notifications working perfectly
- [x] Service Worker active and functional
- [x] Firebase FCM integration working
- [x] Voice alerts working
- [x] Test code removed
- [x] Console logging cleaned up

## 🚀 **SERVER DEPLOYMENT STEPS**

### **1. Upload Files to Server**
```bash
# Upload these key files to your server:
- app/Http/Controllers/PushNotificationController.php
- app/Http/Controllers/StaffController.php (updated methods)
- public/sw.js (unified Service Worker)
- public/js/notifications.js (cleaned up)
- resources/views/layouts/app.blade.php (Firebase integration)
- routes/web.php (cleaned up)
- routes/api.php (FCM token storage)
```

### **2. Configure Firebase Domains**
**CRITICAL STEP - Add Production Domains to Firebase:**

1. **Go to Firebase Console**: https://console.firebase.google.com/
2. **Select project**: `vms-crm-notifications`
3. **Go to "Project Settings"** (gear icon)
4. **Click "General" tab**
5. **Scroll to "Your apps" section**
6. **Find your Web app** and click gear icon
7. **Click "Add domain"**

**Add these production domains:**
```
motion.taskbook.co.in
horizon.taskbook.co.in
```

### **3. Install Dependencies on Server**
```bash
# Install Firebase JWT library
composer require firebase/php-jwt

# Or if composer fails, manual installation:
# Download firebase/php-jwt and place in vendor/firebase/php-jwt/
```

### **4. Configure Firebase on Server**
```bash
# Update these in .env file:
FIREBASE_PROJECT_ID=vms-crm-notifications
FIREBASE_API_KEY=AIzaSyB5H0dX6IxDUAhSYMnqhD5VIighv6N7OX8
FIREBASE_SERVICE_ACCOUNT_EMAIL=firebase-adminsdk-fbsvc@vms-crm-notifications.iam.gserviceaccount.com
FIREBASE_SERVICE_ACCOUNT_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC1Yl9hJZegfzuu\nN+hha+NC9TDoLRAkWfLRLoOI1mUkOYjWsmf/F0rjmMOentPN/is+JAwMjFY8gTjq\nyu08UprjrcE1f1VsEElicDszjVVCKh8jVAZIV9ZoDm/EWvRA30AAolteDE4uUACF\n/yYDBap6rqjgOXjEwOEbEQi5bMzuyoxsfKpSExqii9ofWplOWxKuljndIcBQakco\ndYbAFDve/9Izt+HaiDpV104iDxs+AWAtxWt9Gvf2j2i1JklQK/5F9o+457ydL3jA\naqkUSOF+eUufUO4FvLMthqoBnouUxZ7An7BXzv5ZPMLM8PC8i3i5vy1dzQLEYnjy\nx0U7Jnd3AgMBAAECggEAGOH2YK7WBqv9pXBA/kBdLF3TiD5KVRpLz7t4SujSdi44\nYe+Wia2J1gAqcdOrDbq89ujeCEimOeWmR7tv4RMZ8XrwIuUldE4lqw3naTKNzCZY\nIDISLJF0NdEpLwAlOtMFhjC/pP6+KOdLsxYmAksgMHVOcHgh46fsGZj0H+/Xizhd\n+5iIKL2T1+AFEv0J40oIh2ly5yJ2se5xHY66h4qnIZftB65rNGySKAkUOozZBZdd\nTSsGY83y6Bpt0qyw5hzUSf5LNdFODInBggzEFqqg7w8cAXfPguEb6OPjaM5zdLb2\nErrtl1F17qxCt0szuicsqs22h60a/j4yzuPLr967RQKBgQDaOcutEBAstYaSH8Db\nNPN4fm8heVFZOdB3C7axHHt7FccMpfXc6KQiAGEwP5bdmBHincTmugON8E4SJh4t\nq87squa+a/jUS+v4okb0rLYxUhEHJ43xn+1FQuyU1rPuOXn8FfvCl/u0pa121WtX\n8ilwBv6w3kNEz+DNNmDRLT36SwKBgQDUyAaE+UuJeBu00+c5p64GYObVZrjm3t9R\nX08cwrp2QuCgF6zzkNBGRj/SlITA2J+UcNsZ+StMLoI0g7Zw5nmPcIhjFjgTlsLh\nPEznQxAAaMWiLNm5MUV59g3sM5dGzbyg1rgFxlgzIa3smq7eumIRTFUgwaqN1mdN\nT93finU8BQKBgBocxFRnEahn6Dxf9FHGmkOWzXFx9Nv6YQl9q1SyFcx6pKDM0wim\nBc3Twc1mLoVBhxJY0pDRPU+kq5LcYMwSPOZw5L9waAvvMcNEl7z7Vam9KjBy+Tcq\nbdfV1D1TG6Cr2/7gGooEaagKEyGfFAMoBPFUxPEhB2eagEnN8fPVuA7VAoGBALzc\nAXVbNCGC+syIXK4+12aP8bKt5yX74aj++GAlsoyvFWLjQL465bHKPnGxIxdr7lA5\nzy8Bit2mVik4UuFon7KiBlw0Z3dzk+uIsxV836INXIVyW5lVUz5KF9dzfyz4BRmZ\nG2L8xmIz3YSpUtccBVknMFPPsYsNJ0lmvx7fbOjlAoGAMq+lHuDxOIbgzib1vN6A\nz2aT3TDE2j2IqvFCWwDdYTR/+CLthXSGQCCYiunxiFc7sl/FMRIaVWECqusipfKQ\nmE6SDswP9CbR2Ai71xgmqp1Q9FYKx+fWqVlMwnec1i/fzy826gjBEqOfxVf51V57\nwmkdZwhZIiACDf/1uCvxzlU=\n-----END PRIVATE KEY-----\n"
```

### **4. Clear Server Cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **5. Test on Server**
1. **Visit your server URL**
2. **Login to the system**
3. **Check if Firebase notifications are enabled in sidebar**
4. **Test by creating a new visitor assignment**
5. **Verify desktop notifications appear**
6. **Test voice alerts**

## 🔧 **TROUBLESHOOTING ON SERVER**

### **If Notifications Don't Work:**
1. **Check browser console for errors**
2. **Verify Firebase configuration**
3. **Check if Service Worker is registered**
4. **Test notification permissions**

### **If Composer Fails:**
```bash
# Manual installation of firebase/php-jwt
cd vendor
mkdir -p firebase/php-jwt
# Download and extract firebase/php-jwt library
```

## 📋 **FINAL VERIFICATION**
- [ ] Desktop notifications working
- [ ] Voice alerts working  
- [ ] Service Worker active
- [ ] Firebase FCM integration working
- [ ] No console errors
- [ ] All test code removed

## 🎉 **SUCCESS!**
Your push notification system is now ready for production use!
