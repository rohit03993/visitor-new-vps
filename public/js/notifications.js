/**
 * Real-Time Notification System
 * Handles browser notifications, voice alerts, and real-time updates
 */

class NotificationSystem {
    constructor() {
        this.isSupported = this.checkSupport();
        this.permissionGranted = false;
        this.voiceEnabled = true;
        this.pollingInterval = null;
        this.lastCheckTime = new Date();
        
        // Smart refresh tracking
        this.lastAssignedCount = 0;
        this.lastAssignedUpdate = '';
        this.isAssignedPage = false;
        
        if (this.isSupported) {
            this.init();
        }
    }

    /**
     * Check if browser supports notifications
     */
    checkSupport() {
        return 'Notification' in window && 'serviceWorker' in navigator;
    }

    /**
     * Initialize notification system
     */
    async init() {
        try {
            console.log('🔔 Initializing notification system...');
            
            // Request notification permission
            const permissionGranted = await this.requestPermission();
            console.log('🔔 Permission granted:', permissionGranted);
            
            // Start checking for notifications
            this.startNotificationCheck();
            console.log('🔔 Notification checking started');
            
            // Add event listeners
            this.addEventListeners();
            console.log('🔔 Event listeners added');
            
            // Test voice synthesis availability
            if ('speechSynthesis' in window) {
                console.log('🔊 Speech synthesis available');
                const voices = speechSynthesis.getVoices();
                console.log('🔊 Available voices:', voices.length);
            } else {
                console.log('❌ Speech synthesis not available');
            }
            
            // Detect current page
            this.isAssignedPage = window.location.pathname.includes('assigned-to-me');
            
            // Initialize assigned page state if on that page
            if (this.isAssignedPage) {
                this.initializeAssignedPageState();
            }
            
            console.log('✅ Smart notification system initialized successfully');
            console.log('🔔 System features:');
            console.log('   • Real-time notifications for new assignments');
            console.log('   • Smart refresh - only when data actually changes');
            console.log('   • No unnecessary page reloads');
            console.log('   • Efficient resource usage');
        } catch (error) {
            console.error('❌ Failed to initialize notification system:', error);
        }
    }

    /**
     * Request notification permission from user
     */
    async requestPermission() {
        if (!this.isSupported) {
            console.warn('⚠️ Notifications not supported in this browser');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            this.permissionGranted = permission === 'granted';
            
            if (this.permissionGranted) {
                console.log('✅ Notification permission granted');
            } else {
                console.log('❌ Notification permission denied');
            }
            
            return this.permissionGranted;
        } catch (error) {
            console.error('❌ Error requesting notification permission:', error);
            return false;
        }
    }

    /**
     * Start lightweight notification checking
     */
    startNotificationCheck() {
        // Check for any pending notifications immediately
        this.checkForNotifications();
        
        // Start simple polling every 15 seconds (lightweight)
        this.startLightweightPolling();
        
        console.log('🔔 Lightweight notification system started');
        console.log('🔔 Checking for notifications every 15 seconds');
    }

    /**
     * Initialize assigned page state tracking
     */
    initializeAssignedPageState() {
        try {
            // Get current count from the page
            const countElement = document.querySelector('.h4.mb-0');
            if (countElement) {
                this.lastAssignedCount = parseInt(countElement.textContent) || 0;
                console.log('📊 Initial assigned count:', this.lastAssignedCount);
            }
            
            // Set initial timestamp
            this.lastAssignedUpdate = new Date().toISOString();
            console.log('📅 Initialized assigned page tracking');
        } catch (error) {
            console.error('❌ Error initializing assigned page state:', error);
        }
    }

    /**
     * Start smart polling - lightweight checks with conditional refresh
     */
    startLightweightPolling() {
        console.log('🔄 Starting smart polling mode (every 15 seconds)');
        
        // Clear any existing interval
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        // Smart polling every 15 seconds
        this.pollingInterval = setInterval(() => {
            this.smartCheck();
        }, 15000);
    }

    /**
     * Smart check - notifications + conditional assigned list refresh
     */
    async smartCheck() {
        try {
            // Always check for notifications
            await this.checkForNotifications();
            
            // Only check assigned changes if on assigned page
            if (this.isAssignedPage) {
                await this.checkAssignedChanges();
            }
        } catch (error) {
            console.error('❌ Error in smart check:', error);
        }
    }

    /**
     * Check for new notifications and refresh assigned list if needed
     */
    async checkForNotifications() {
        try {
            console.log('🔔 Checking for pending notifications...');
            const response = await fetch('/staff/notifications/get', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Debug logging
            console.log('🔍 Notification response:', data);
            
            if (data.success && data.notifications && data.notifications.length > 0) {
                console.log(`🔔 Found ${data.notifications.length} pending notifications`);
                console.log('🔍 Notifications data:', data.notifications);
                
                // Process each notification
                data.notifications.forEach((notification, index) => {
                    console.log(`🔔 Processing notification ${index + 1}:`, notification);
                    console.log(`👤 Notification for user: ${notification.user_name} (ID: ${notification.user_id})`);
                    console.log(`📋 Message: ${notification.message}`);
                    
                    this.showNotification(notification);
                    
                    // If it's a visit assignment, refresh the assigned list
                    if (notification.type === 'visit_assigned') {
                        this.refreshAssignedToMeList();
                    }
                });
            } else {
                console.log('🔔 No pending notifications found');
                console.log('🔍 Debug info:', data.debug || 'No debug info');
                if (data.success === false) {
                    console.error('❌ Notification API returned error:', data.message);
                }
            }
            
            // Note: Smart assigned checking is now handled separately in smartCheck()
            
        } catch (error) {
            console.error('❌ Error checking for notifications:', error);
        }
    }

    /**
     * Smart check for assigned list changes (lightweight)
     */
    async checkAssignedChanges() {
        try {
            console.log('🔍 Smart check: Looking for assigned list changes...');
            
            const url = `/staff/check-assigned-changes?last_count=${this.lastAssignedCount}&last_update=${encodeURIComponent(this.lastAssignedUpdate)}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                if (data.has_changes) {
                    console.log('🔄 Changes detected! Refreshing assigned list...');
                    console.log(`📊 Count changed: ${this.lastAssignedCount} → ${data.current_count}`);
                    
                    // Update tracking variables
                    this.lastAssignedCount = data.current_count;
                    this.lastAssignedUpdate = data.last_update || new Date().toISOString();
                    
                    // Refresh the list
                    await this.refreshAssignedToMeList();
                } else {
                    console.log('✅ No changes in assigned list');
                }
            } else {
                console.error('❌ Failed to check assigned changes:', data.message);
            }
        } catch (error) {
            console.error('❌ Error checking assigned changes:', error);
            // Fallback: refresh anyway if there's an error
            await this.refreshAssignedToMeList();
        }
    }

    /**
     * Refresh the "Assigned to Me" list without page reload
     */
    async refreshAssignedToMeList() {
        try {
            // Only refresh if we're on the assigned-to-me page
            if (!window.location.pathname.includes('assigned-to-me')) {
                return;
            }

            console.log('🔄 Refreshing assigned interactions list...');
            
            const response = await fetch('/staff/assigned-to-me', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const html = await response.text();
                
                // Extract the main content from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('.container-fluid');
                
                if (newContent) {
                    // Replace the current content
                    const currentContent = document.querySelector('.container-fluid');
                    if (currentContent) {
                        currentContent.innerHTML = newContent.innerHTML;
                        console.log('✅ Assigned list refreshed successfully');
                        
                        // Update tracking state after refresh
                        this.updateAssignedPageState();
                        
                        // Only show refresh indicator if this was a manual refresh
                        if (this.isManualRefresh) {
                            this.showRefreshIndicator();
                            this.isManualRefresh = false;
                        }
                    }
                }
            }
        } catch (error) {
            console.error('❌ Error refreshing assigned list:', error);
        }
    }

    /**
     * Update assigned page state after refresh
     */
    updateAssignedPageState() {
        try {
            // Get updated count from the page
            const countElement = document.querySelector('.h4.mb-0');
            if (countElement) {
                const newCount = parseInt(countElement.textContent) || 0;
                console.log(`📊 Updated assigned count: ${this.lastAssignedCount} → ${newCount}`);
                this.lastAssignedCount = newCount;
            }
            
            // Update timestamp
            this.lastAssignedUpdate = new Date().toISOString();
        } catch (error) {
            console.error('❌ Error updating assigned page state:', error);
        }
    }

    /**
     * Show a subtle indicator that the list was refreshed (only for manual refresh)
     */
    showRefreshIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'alert alert-success alert-dismissible fade show position-fixed';
        indicator.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        indicator.innerHTML = `
            <i class="fas fa-sync-alt me-2"></i>
            <strong>List Refreshed!</strong> Manually updated by user.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(indicator);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (indicator.parentElement) {
                indicator.remove();
            }
        }, 3000);
    }

    /**
     * Show notification to user
     */
    showNotification(notification) {
        try {
            // Show desktop notification
            if (this.permissionGranted) {
                this.showDesktopNotification(notification);
            }

            // Play voice alert
            if (this.voiceEnabled) {
                this.playVoiceAlert(notification);
            }

            // Show in-page notification
            this.showInPageNotification(notification);

            console.log('🔔 Notification shown:', notification.title);
        } catch (error) {
            console.error('❌ Error showing notification:', error);
        }
    }

    /**
     * Show desktop notification
     */
    showDesktopNotification(notification) {
        if (!this.permissionGranted) {
            console.log('❌ Desktop notifications not permitted');
            return;
        }

        console.log('🔔 Showing desktop notification:', notification.title);

        const options = {
            body: notification.message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            tag: notification.type + '_' + Date.now(), // Unique tag to prevent replacement
            requireInteraction: true,
            silent: false, // Make sure it makes a sound
            vibrate: [200, 100, 200], // Vibration pattern for mobile
            actions: [
                {
                    action: 'view',
                    title: 'View Details'
                },
                {
                    action: 'dismiss',
                    title: 'Dismiss'
                }
            ]
        };

        try {
            const desktopNotification = new Notification(notification.title, options);

            // Handle notification click
            desktopNotification.onclick = function() {
                console.log('🔔 Desktop notification clicked');
                window.focus();
                desktopNotification.close();
                
                // If it's a visit assignment, you could redirect to the visitor profile
                if (notification.type === 'visit_assigned' && notification.data.interaction_id) {
                    console.log('Visit assigned notification clicked:', notification.data);
                    // You can add custom logic here to redirect to specific page
                }
            };

            // Handle notification show
            desktopNotification.onshow = function() {
                console.log('🔔 Desktop notification shown');
            };

            // Handle notification error
            desktopNotification.onerror = function(error) {
                console.error('❌ Desktop notification error:', error);
            };

            // Auto-close after 15 seconds
            setTimeout(() => {
                desktopNotification.close();
            }, 15000);

        } catch (error) {
            console.error('❌ Error creating desktop notification:', error);
        }
    }

    /**
     * Play voice alert
     */
    playVoiceAlert(notification) {
        if (!this.voiceEnabled) {
            console.log('🔇 Voice alerts disabled');
            return;
        }

        try {
            // Check if speech synthesis is supported
            if ('speechSynthesis' in window) {
                // Stop any current speech
                speechSynthesis.cancel();
                
                const utterance = new SpeechSynthesisUtterance();
                
                // Customize voice message based on notification type
                let voiceMessage = notification.message;
                
                if (notification.type === 'visit_assigned') {
                    voiceMessage = `You Have a New Interaction - Thank you.`;
                } else if (notification.type === 'test') {
                    voiceMessage = `Test notification! The notification system is working properly.`;
                }

                utterance.text = voiceMessage;
                utterance.volume = 1.0; // Maximum volume
                utterance.rate = 0.8; // Slightly slower for clarity
                utterance.pitch = 1.0;

                // Wait for voices to load, then select best voice
                const speakWithVoice = () => {
                    const voices = speechSynthesis.getVoices();
                    console.log('Available voices:', voices.length);
                    
                    // Try to find a good English voice
                    let selectedVoice = voices.find(voice => 
                        voice.lang.startsWith('en-US') && 
                        (voice.name.includes('Google') || voice.name.includes('Microsoft') || voice.name.includes('Samantha'))
                    );
                    
                    if (!selectedVoice) {
                        selectedVoice = voices.find(voice => voice.lang.startsWith('en'));
                    }
                    
                    if (selectedVoice) {
                        utterance.voice = selectedVoice;
                        console.log('Using voice:', selectedVoice.name);
                    } else {
                        console.log('Using default voice');
                    }
                    
                    // Add event listeners
                    utterance.onstart = () => console.log('🔊 Voice alert started');
                    utterance.onend = () => console.log('🔊 Voice alert ended');
                    utterance.onerror = (e) => console.error('❌ Voice alert error:', e);
                    
                    speechSynthesis.speak(utterance);
                    console.log('🔊 Voice alert triggered:', voiceMessage);
                };

                // If voices are already loaded, speak immediately
                if (speechSynthesis.getVoices().length > 0) {
                    speakWithVoice();
                } else {
                    // Wait for voices to load
                    speechSynthesis.onvoiceschanged = speakWithVoice;
                }
            } else {
                console.log('❌ Speech synthesis not supported');
            }
        } catch (error) {
            console.error('❌ Error playing voice alert:', error);
        }
    }

    /**
     * Show in-page notification
     */
    showInPageNotification(notification) {
        console.log('🔔 Showing in-page notification:', notification.title);

        // Create notification element
        const notificationElement = document.createElement('div');
        notificationElement.className = 'notification-toast';
        notificationElement.setAttribute('data-type', notification.type);
        
        // Add appropriate icon based on notification type
        let iconClass = 'fas fa-bell';
        if (notification.type === 'visit_assigned') {
            iconClass = 'fas fa-user-plus';
        } else if (notification.type === 'test') {
            iconClass = 'fas fa-flask';
        }

        notificationElement.innerHTML = `
            <div class="notification-content">
                <div class="notification-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="notification-text">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                </div>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        // Add to page
        let notificationContainer = document.getElementById('notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.className = 'notification-container';
            document.body.appendChild(notificationContainer);
        }

        notificationContainer.appendChild(notificationElement);

        // Add a subtle animation effect
        notificationElement.style.transform = 'translateX(100%)';
        notificationElement.style.opacity = '0';

        // Animate in
        setTimeout(() => {
            notificationElement.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            notificationElement.style.transform = 'translateX(0)';
            notificationElement.style.opacity = '1';
            notificationElement.classList.add('show');
        }, 100);

        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notificationElement.parentElement) {
                notificationElement.style.transform = 'translateX(100%)';
                notificationElement.style.opacity = '0';
                setTimeout(() => {
                    if (notificationElement.parentElement) {
                        notificationElement.remove();
                    }
                }, 300);
            }
        }, 10000);

        console.log('🔔 In-page notification added to DOM');
    }

    /**
     * Add event listeners
     */
    addEventListeners() {
        // Listen for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('📱 Page hidden - notifications will still work');
            } else {
                console.log('📱 Page visible - checking for missed notifications');
                this.checkForNotifications();
            }
        });

        // Listen for online/offline status
        window.addEventListener('online', () => {
            console.log('🌐 Back online - resuming notifications');
            this.checkForNotifications();
        });

        window.addEventListener('offline', () => {
            console.log('🌐 Gone offline - notifications will resume when back online');
        });
    }

    /**
     * Stop checking for notifications
     */
    stopNotificationCheck() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
            console.log('🔔 Stopped notification polling');
        }
    }

    /**
     * Enable/disable voice alerts
     */
    toggleVoice(enabled) {
        this.voiceEnabled = enabled;
        console.log(`🔊 Voice alerts ${enabled ? 'enabled' : 'disabled'}`);
    }

    /**
     * Test notification system
     */
    testNotification() {
        const testNotification = {
            type: 'test',
            title: '🔔 Notification System Test',
            message: 'This is a test notification to verify the system is working properly with voice alerts!',
            data: {
                test: true,
                timestamp: new Date().toISOString()
            },
            timestamp: new Date().toISOString()
        };

        this.showNotification(testNotification);
    }

    /**
     * Cleanup
     */
    destroy() {
        this.stopNotificationCheck();
        console.log('🔔 Notification system destroyed');
    }
}

// Initialize notification system when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize for authenticated users
    if (document.body.classList.contains('authenticated')) {
        window.notificationSystem = new NotificationSystem();
        
        // Notification system ready
        console.log('✅ Notification system initialized successfully');
    }
});

// Global test functions for console debugging
window.testNotification = function() {
    console.log('🔔 Testing notification system...');
    if (window.notificationSystem) {
        window.notificationSystem.testNotification();
    } else {
        console.error('❌ Notification system not initialized');
    }
};

window.testVoice = function() {
    console.log('🔊 Testing voice synthesis...');
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance('Hello! This is a test of the voice notification system.');
        utterance.volume = 1.0;
        utterance.rate = 0.8;
        speechSynthesis.speak(utterance);
        console.log('🔊 Voice test triggered');
    } else {
        console.error('❌ Speech synthesis not supported');
    }
};

window.checkNotificationPermission = function() {
    console.log('🔔 Checking notification permission...');
    if ('Notification' in window) {
        console.log('Permission:', Notification.permission);
        if (Notification.permission === 'granted') {
            console.log('✅ Notifications are enabled');
        } else if (Notification.permission === 'denied') {
            console.log('❌ Notifications are blocked');
        } else {
            console.log('⚠️ Permission not requested yet');
        }
    } else {
        console.error('❌ Notifications not supported');
    }
};

window.checkPollingStatus = function() {
    console.log('🔄 Checking notification polling status...');
    if (window.notificationSystem && window.notificationSystem.pollingInterval) {
        console.log('✅ Polling is active (every 15 seconds)');
    } else {
        console.log('❌ Polling is not active');
    }
};

// Global function for manual refresh
window.refreshAssignedList = function() {
    console.log('🔄 Manual refresh triggered...');
    if (window.notificationSystem) {
        // Mark as manual refresh to show indicator
        window.notificationSystem.isManualRefresh = true;
        window.notificationSystem.refreshAssignedToMeList();
    } else {
        // Fallback: reload the page
        window.location.reload();
    }
};

// Debug function to manually check notifications
window.debugNotifications = function() {
    console.log('🔍 Debug: Manually checking for notifications...');
    if (window.notificationSystem) {
        window.notificationSystem.checkForNotifications();
    } else {
        console.error('❌ Notification system not available');
    }
};

// Debug function to create a test notification using direct route
window.createTestNotification = async function() {
    console.log('🧪 Creating test notification using debug route...');
    try {
        const response = await fetch('/debug-create-notification', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        console.log('🧪 Debug notification result:', result);
        
        if (result.success) {
            console.log('✅ Test notification created successfully');
            console.log('📋 Session data:', result.session_data);
            
            // Check for it immediately
            setTimeout(() => {
                console.log('🔍 Now checking for the notification...');
                debugNotifications();
            }, 1000);
        } else {
            console.error('❌ Failed to create test notification:', result);
        }
    } catch (error) {
        console.error('❌ Error creating test notification:', error);
    }
};

// Simple function to test notification display directly
window.testNotificationDisplay = function() {
    console.log('🧪 Testing notification display directly...');
    
    const testNotification = {
        type: 'visit_assigned',
        title: 'Test Notification',
        message: 'This is a direct test of the notification display system',
        data: {
            visitor_name: 'Test Visitor',
            purpose: 'Test Purpose'
        },
        timestamp: new Date().toISOString()
    };
    
    if (window.notificationSystem) {
        window.notificationSystem.showNotification(testNotification);
        console.log('✅ Test notification displayed directly');
    } else {
        console.error('❌ Notification system not available');
    }
};

// Test if API routes are working
window.testAPI = async function() {
    console.log('🧪 Testing API connectivity...');
    try {
        const response = await fetch('/test-api', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            console.log('✅ API is working:', data);
            
            // Now test notification endpoint
            console.log('🧪 Testing notification endpoint...');
            const notifResponse = await fetch('/staff/notifications/get', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (notifResponse.ok) {
                const notifData = await notifResponse.json();
                console.log('✅ Notification endpoint working:', notifData);
            } else {
                console.error('❌ Notification endpoint failed:', notifResponse.status);
            }
        } else {
            console.error('❌ API test failed:', response.status);
        }
    } catch (error) {
        console.error('❌ API test error:', error);
    }
};

// Export for use in other scripts
window.NotificationSystem = NotificationSystem;
