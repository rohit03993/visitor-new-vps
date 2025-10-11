// Service Worker - UNIFIED NOTIFICATION SYSTEM
// Version: 4.0.0 - Firebase + PWA notifications enabled

console.log('🔧 Service Worker loaded - UNIFIED NOTIFICATION SYSTEM');

// Import Firebase SDK
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Firebase configuration - MUST MATCH FRONTEND
const firebaseConfig = {
    apiKey: "AIzaSyB5H0dX6IxDUAhSYMnqhD5VIighv6N7OX8",
    authDomain: "vms-crm-notifications.firebaseapp.com",
    projectId: "vms-crm-notifications",
    storageBucket: "vms-crm-notifications.firebasestorage.app",
    messagingSenderId: "197047969653",
    appId: "1:197047969653:web:785933db1521840ffa953c",
    measurementId: "G-FP86BQXRYR"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handle background messages from Firebase
messaging.onBackgroundMessage(function(payload) {
    console.log('📨 Received background message:', payload);
    console.log('📱 Mobile background message received');
    
    // Only show notifications from our unified system
    if (payload.data && payload.data.source === 'unified_notification') {
        const notificationTitle = payload.notification?.title || payload.data?.title || 'VMS CRM';
        const notificationOptions = {
            body: payload.notification?.body || payload.data?.body || 'You have a new notification',
            icon: '/favicon.svg',
            badge: '/favicon.svg',
            tag: 'vms-notification',
            requireInteraction: false,
            silent: false, // Allow sound
            data: payload.data || {},
            // Mobile-specific options
            actions: [
                {
                    action: 'open',
                    title: 'Open App',
                    icon: '/favicon.svg'
                },
                {
                    action: 'close',
                    title: 'Close',
                    icon: '/favicon.svg'
                }
            ],
            vibrate: [200, 100, 200], // Mobile vibration pattern
            timestamp: Date.now()
        };
        
        console.log('📱 Mobile notification options:', notificationOptions);
        return self.registration.showNotification(notificationTitle, notificationOptions);
    }
    
    console.log('📨 Notification filtered out - not from unified system');
});

// Handle notification clicks
self.addEventListener('notificationclick', function(event) {
    console.log('📨 Notification clicked:', event.notification.tag);
    
    event.notification.close();
    
    // Handle click based on notification data
    if (event.notification.data && event.notification.data.url) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    } else {
        // Default action - open the app
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

console.log('✅ Service Worker ready - unified notification system active');
