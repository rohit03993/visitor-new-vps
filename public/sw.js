/**
 * Service Worker for VMS CRM - UNIFIED NOTIFICATION SYSTEM
 * Handles both Firebase FCM and custom push notifications
 * Version: 2.0.0 - Production Ready (Test code removed)
 */

// Import Firebase scripts for FCM support
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Firebase configuration - Auto-detects environment
const isProduction = self.location.hostname !== 'localhost' && self.location.hostname !== '127.0.0.1';
const firebaseConfig = {
    apiKey: "AIzaSyB5H0dX6IxDUAhSYMnqhD5VIighv6N7OX8",
    authDomain: "vms-crm-notifications.firebaseapp.com",
    projectId: "vms-crm-notifications",
    storageBucket: "vms-crm-notifications.firebasestorage.app",
    messagingSenderId: "197047969653",
    appId: "1:197047969653:web:785933db1521840ffa953c",
    measurementId: "G-FP86BQXRYR"
};

// Log environment for debugging
console.log('🌍 Service Worker Environment:', isProduction ? 'Production' : 'Development');
console.log('🌍 Service Worker Hostname:', self.location.hostname);

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Initialize Firebase Messaging
const messaging = firebase.messaging();

console.log('🔧 Unified Service Worker loaded with Firebase support');

// Handle Firebase background messages
messaging.onBackgroundMessage((payload) => {
    console.log('📨 Firebase background message received:', payload);
    
    const notificationTitle = payload.notification?.title || 'VMS CRM Notification';
    const notificationOptions = {
        body: payload.notification?.body || 'You have a new notification',
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        tag: 'firebase-notification',
        requireInteraction: true,
        silent: false,
        vibrate: [200, 100, 200, 100, 200],
        data: {
            ...payload.data,
            url: payload.data?.url || '/staff/assigned-to-me',
            timestamp: Date.now()
        },
        actions: [
            {
                action: 'open',
                title: '📱 Open App',
                icon: '/favicon.ico'
            },
            {
                action: 'view',
                title: '👀 View Assignment',
                icon: '/favicon.ico'
            },
            {
                action: 'dismiss',
                title: '❌ Dismiss',
                icon: '/favicon.ico'
            }
        ]
    };

    console.log('🔔 Showing Firebase background notification:', notificationTitle);
    
    self.registration.showNotification(notificationTitle, notificationOptions)
        .then(() => {
            console.log('✅ Firebase background notification shown successfully');
        })
        .catch(error => {
            console.error('❌ Error showing Firebase background notification:', error);
        });
});

// Install event - minimal setup
self.addEventListener('install', event => {
    console.log('🔧 Push Service Worker installing...');
    
    event.waitUntil(
        Promise.resolve().then(() => {
            console.log('✅ Push Service Worker installed successfully');
            return self.skipWaiting();
        })
    );
});

// Activate event - minimal cleanup
self.addEventListener('activate', event => {
    console.log('🚀 Push Service Worker activating...');
    
    event.waitUntil(
        Promise.resolve().then(() => {
            console.log('✅ Push Service Worker activated successfully');
            return self.clients.claim();
        })
    );
});

// Push event - handle push notifications
self.addEventListener('push', event => {
    console.log('📨 Push event received:', event);
    
    let notificationData = {
        title: 'VMS CRM - New Assignment',
        body: 'You have a new visit assignment',
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        tag: 'vms-visit-assignment',
        requireInteraction: true,
        vibrate: [200, 100, 200, 100, 200],
        silent: false, // Ensure sound plays
        data: {
            url: '/staff/assigned-to-me',
            timestamp: Date.now()
        },
        actions: [
            {
                action: 'open',
                title: '📱 Open App',
                icon: '/favicon.ico'
            },
            {
                action: 'view',
                title: '👀 View Assignment',
                icon: '/favicon.ico'
            },
            {
                action: 'dismiss',
                title: '❌ Dismiss',
                icon: '/favicon.ico'
            }
        ]
    };

    // Parse push data if available
    if (event.data) {
        try {
            const pushData = event.data.json();
            notificationData = {
                ...notificationData,
                title: pushData.title || 'VMS CRM - New Assignment',
                body: pushData.body || pushData.message || 'You have a new visit assignment',
                icon: pushData.icon || '/favicon.ico',
                badge: pushData.badge || '/favicon.ico',
                tag: pushData.tag || pushData.type || 'vms-visit-assignment',
                data: {
                    ...notificationData.data,
                    ...pushData.data,
                    url: pushData.url || '/staff/assigned-to-me'
                }
            };
        } catch (error) {
            console.error('❌ Error parsing push data:', error);
            // Use default notification data
        }
    }

    console.log('🔔 Showing app-like notification:', notificationData);

    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
            .then(() => {
                console.log('✅ App-like notification shown successfully');
            })
            .catch(error => {
                console.error('❌ Error showing notification:', error);
            })
    );
});

// Notification click event
self.addEventListener('notificationclick', event => {
    console.log('🔔 App notification clicked:', event);
    
    event.notification.close();

    if (event.action === 'open' || !event.action) {
        // Open the app
        event.waitUntil(
            clients.matchAll({ type: 'window' }).then(clientList => {
                // Check if app is already open
                for (let i = 0; i < clientList.length; i++) {
                    const client = clientList[i];
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        console.log('🔄 Focusing existing VMS app window');
                        return client.focus();
                    }
                }
                
                // Open new window if app is not open
                if (clients.openWindow) {
                    console.log('🆕 Opening new VMS app window');
                    return clients.openWindow('/login');
                }
            })
        );
    } else if (event.action === 'view') {
        // Open assignments page
        event.waitUntil(
            clients.matchAll({ type: 'window' }).then(clientList => {
                // Check if app is already open
                for (let i = 0; i < clientList.length; i++) {
                    const client = clientList[i];
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        console.log('🔄 Focusing app and navigating to assignments');
                        client.postMessage({ action: 'navigate', url: '/staff/assigned-to-me' });
                        return client.focus();
                    }
                }
                
                // Open new window to assignments page
                if (clients.openWindow) {
                    console.log('🆕 Opening assignments page');
                    return clients.openWindow('/staff/assigned-to-me');
                }
            })
        );
    } else if (event.action === 'dismiss') {
        console.log('❌ Notification dismissed');
        // Just close the notification (already done above)
    }
});

// Fetch event - DISABLED to prevent authentication interference
// self.addEventListener('fetch', event => {
//     // DISABLED: This was interfering with authentication requests
//     // Only handle push notifications, not regular requests
// });

// Background sync event (for future use)
self.addEventListener('sync', event => {
    console.log('🔄 Background sync event:', event.tag);
    
    if (event.tag === 'background-sync') {
        event.waitUntil(
            // Handle background sync logic here
            syncData()
        );
    }
});

// Helper function for background sync
async function syncData() {
    try {
        console.log('🔄 Syncing data in background...');
        
        // Add your background sync logic here
        // For example, sync pending notifications, upload offline data, etc.
        
        console.log('✅ Background sync completed');
    } catch (error) {
        console.error('❌ Background sync failed:', error);
    }
}

// Message event - handle messages from main thread
self.addEventListener('message', event => {
    console.log('📨 Message received in Service Worker:', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    // Test notification functionality removed for production
});

console.log('✅ Service Worker script loaded successfully');
