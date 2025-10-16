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
            // Register Service Worker
            await this.registerServiceWorker();
            
            // Request notification permission
            const permissionGranted = await this.requestPermission();
            
            // Initialize Firebase notifications (handled in app.blade.php)
            
            // Start checking for notifications
            this.startNotificationCheck();
            
            // Add event listeners
            this.addEventListeners();
            
            // Setup PWA features
            this.setupPWAFeatures();
            console.log('📱 PWA features initialized');
            
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
            
            // Smart notification system initialized successfully
        } catch (error) {
            console.error('❌ Failed to initialize notification system:', error);
        }
    }

    /**
     * Register Service Worker - For PWA features only
     */
    async registerServiceWorker() {
        console.log('🔧 Registering Service Worker for PWA features...');
        
        if ('serviceWorker' in navigator) {
            try {
                // Register the Service Worker for PWA features only
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('✅ Service Worker registered successfully for PWA:', registration.scope);
                
                // Handle updates
                registration.addEventListener('updatefound', () => {
                    console.log('🔄 Service Worker update found');
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('🆕 New Service Worker installed, reload to activate');
                        }
                    });
                });
                
                return registration;
            } catch (error) {
                console.error('❌ Service Worker registration failed:', error);
                return null;
            }
        } else {
            console.log('❌ Service Worker not supported in this browser');
            return null;
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
     * Setup enhanced PWA features for mobile
     */
    setupPWAFeatures() {
        console.log('📱 Setting up enhanced PWA features...');
        
        // Add to Home Screen prompt
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('📱 PWA install prompt available');
            
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            
            // Show custom install button with mobile optimization
            this.showEnhancedInstallPrompt(deferredPrompt);
        });

        // Handle app installed
        window.addEventListener('appinstalled', (evt) => {
            console.log('✅ PWA app installed successfully');
            this.hideInstallPrompt();
            
            // Show success message
            this.showPWASuccessMessage();
        });

        // Enhanced mobile detection and features
        if (this.isMobileDevice()) {
            this.setupMobilePWAFeatures();
        }
    }

    /**
     * Setup mobile-specific PWA features
     */
    setupMobilePWAFeatures() {
        console.log('📱 Setting up mobile-specific PWA features...');
        
        // Add mobile-specific meta tags dynamically
        this.addMobileMetaTags();
        
        // Setup mobile notification handling
        this.setupMobileNotifications();
        
        // Add mobile-specific styles
        this.addMobileStyles();
    }

    /**
     * Add mobile-specific meta tags
     */
    addMobileMetaTags() {
        const metaTags = [
            { name: 'mobile-web-app-capable', content: 'yes' },
            { name: 'apple-mobile-web-app-capable', content: 'yes' },
            { name: 'apple-mobile-web-app-status-bar-style', content: 'default' },
            { name: 'format-detection', content: 'telephone=no' }
        ];

        metaTags.forEach(tag => {
            let meta = document.querySelector(`meta[name="${tag.name}"]`);
            if (!meta) {
                meta = document.createElement('meta');
                meta.name = tag.name;
                meta.content = tag.content;
                document.head.appendChild(meta);
            }
        });
    }

    /**
     * Setup mobile-specific notifications
     */
    setupMobileNotifications() {
        // Enhanced mobile notification handling
        if ('Notification' in window && Notification.permission === 'granted') {
            console.log('📱 Mobile notifications enabled');
            
            // Add mobile-specific notification settings
            this.mobileNotificationSettings = {
                vibrate: true,
                sound: true,
                badge: true,
                persistent: true
            };
        }
    }

    /**
     * Add mobile-specific styles
     */
    addMobileStyles() {
        const style = document.createElement('style');
        style.textContent = `
            /* Mobile notification styles */
            @media (max-width: 768px) {
                .notification-toast {
                    font-size: 16px;
                    padding: 16px;
                    margin: 10px;
                    border-radius: 12px;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                }
                
                .notification-toast .toast-header {
                    font-size: 18px;
                    font-weight: 600;
                }
                
                .notification-toast .toast-body {
                    font-size: 16px;
                    line-height: 1.5;
                }
            }
            
            /* PWA install button styles */
            .pwa-install-btn {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 10000;
                background: linear-gradient(135deg, #007bff, #0056b3);
                color: white;
                border: none;
                border-radius: 25px;
                padding: 12px 20px;
                font-size: 14px;
                font-weight: 600;
                box-shadow: 0 4px 20px rgba(0,123,255,0.4);
                cursor: pointer;
                transition: all 0.3s ease;
                animation: pulse 2s infinite;
            }
            
            .pwa-install-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 25px rgba(0,123,255,0.6);
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
        `;
        
        document.head.appendChild(style);
    }

    /**
     * Show enhanced install prompt
     */
    showEnhancedInstallPrompt(deferredPrompt) {
        // Create enhanced install button
        const installBtn = document.createElement('button');
        installBtn.innerHTML = this.isMobileDevice() ? '📱 Install App' : '💻 Install App';
        installBtn.className = 'pwa-install-btn';
        
        installBtn.onclick = () => {
            // Show the install prompt
            deferredPrompt.prompt();
            
            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('✅ User accepted the install prompt');
                    this.showInstallSuccessMessage();
                } else {
                    console.log('❌ User dismissed the install prompt');
                }
                
                // Clear the deferredPrompt
                deferredPrompt = null;
                this.hideInstallPrompt();
            });
        };
        
        document.body.appendChild(installBtn);
        this.installButton = installBtn;
        
        // Auto-hide timing based on device
        const autoHideTime = this.isMobileDevice() ? 15000 : 10000;
        setTimeout(() => {
            this.hideInstallPrompt();
        }, autoHideTime);
    }

    /**
     * Show install success message
     */
    showInstallSuccessMessage() {
        this.showInPageNotification({
            type: 'success',
            title: '🎉 App Installed!',
            message: 'VMS CRM has been installed successfully! You can now access it from your home screen.',
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Show PWA success message
     */
    showPWASuccessMessage() {
        this.showInPageNotification({
            type: 'success',
            title: '🚀 PWA Ready!',
            message: 'Your VMS CRM is now running as a Progressive Web App with enhanced mobile features!',
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Hide install prompt
     */
    hideInstallPrompt() {
        if (this.installButton) {
            this.installButton.remove();
            this.installButton = null;
        }
    }

    /**
     * Start lightweight notification checking - DISABLED FOR UNIFIED SYSTEM
     */
    startNotificationCheck() {
        console.log('📨 File-based polling system disabled - using unified Firebase notifications');
        
        // DISABLED: File-based polling system
        // All notifications now handled by Firebase unified system
        // this.checkForNotifications();
        // this.startLightweightPolling();
        
        // Unified notification system active
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
     * Start enhanced smart polling with visibility detection
     */
    startLightweightPolling() {
        console.log('🔄 Starting enhanced smart polling mode');
        
        // Clear any existing interval
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        // Enhanced polling based on browser state
        this.startSmartPolling();
        
        // Add visibility change detection for better background handling
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.startBackgroundPolling();
            } else {
                this.startActivePolling();
            }
        });
    }

    /**
     * Smart polling based on browser state
     */
    startSmartPolling() {
        if (document.hidden) {
            this.startBackgroundPolling();
        } else {
            this.startActivePolling();
        }
    }

    /**
     * Active polling when browser tab is visible
     */
    startActivePolling() {
        
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        this.pollingInterval = setInterval(() => {
            this.smartCheck();
        }, 10000); // More frequent when active
    }

    /**
     * Background polling when browser tab is hidden
     */
    startBackgroundPolling() {
        
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        this.pollingInterval = setInterval(() => {
            this.smartCheck();
        }, 30000); // Less frequent in background
    }

    /**
     * Smart check - notifications + conditional assigned list refresh - DISABLED FOR UNIFIED SYSTEM
     */
    async smartCheck() {
        console.log('📨 Smart check DISABLED - using unified Firebase notifications');
        
        // DISABLED: This was calling checkForNotifications() which triggered duplicate notifications
        // All notifications now handled by Firebase unified system
        
        // Only check assigned changes if on assigned page (keep this for list refresh)
        if (this.isAssignedPage) {
            await this.checkAssignedChanges();
        }
    }

    /**
     * Check for new notifications and refresh assigned list if needed - DISABLED FOR UNIFIED SYSTEM
     */
    async checkForNotifications() {
        console.log('📨 File-based notification checking DISABLED - using unified Firebase notifications');
        
        // DISABLED: This was causing duplicate notifications
        // All notifications now handled by Firebase unified system
        // The /staff/notifications/get endpoint was triggering additional notifications
        
        // Unified notification system active
        return;
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

            // Note: Firebase push notifications are sent directly from the backend
            // when assignments are created, so no need to send them again here.
            // This prevents duplicate notifications (3 popups → 1 popup)

            console.log('🔔 Notification shown:', notification.title);
        } catch (error) {
            console.error('❌ Error showing notification:', error);
        }
    }

    /**
     * Show enhanced desktop notification with mobile optimization
     */
    showDesktopNotification(notification) {
        if (!this.permissionGranted) {
            console.log('❌ Desktop notifications not permitted');
            return;
        }

        console.log('🔔 Showing enhanced desktop notification:', notification.title);

        try {
            // Enhanced notification options with mobile support
            const notificationOptions = {
            body: notification.message,
            icon: '/favicon.ico',
            badge: '/favicon.ico',
                tag: notification.type + '_' + Date.now(),
                requireInteraction: this.isMobileDevice() ? true : false, // Keep open longer on mobile
                silent: false,
                vibrate: this.isMobileDevice() ? [200, 100, 200, 100, 200] : [200, 100, 200],
                data: notification.data || {},
                actions: this.isMobileDevice() ? [] : [
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

            // Use direct Notification API
            const desktopNotification = new Notification(notification.title, notificationOptions);

            // Enhanced click handling
            desktopNotification.onclick = function() {
                console.log('🔔 Desktop notification clicked');
                
                // Focus window and close notification
                if (window.focus) {
                window.focus();
                }
                desktopNotification.close();
                
                // Mobile-specific handling
                if (notification.type === 'visit_assigned' && notification.data.interaction_id) {
                    console.log('Visit assigned notification clicked:', notification.data);
                    
                    // For mobile, try to open in app if installed
                    if (this.isMobileDevice()) {
                        this.handleMobileNotificationClick(notification);
                }
                }
            }.bind(this);

            // Enhanced event handlers
            desktopNotification.onshow = function() {
                console.log('🔔 Desktop notification shown successfully');
                
                // Mobile-specific feedback
                if (this.isMobileDevice()) {
                    this.playMobileNotificationSound();
                }
            }.bind(this);

            desktopNotification.onerror = function(error) {
                console.error('❌ Desktop notification error:', error);
            };

            // Auto-close timing based on device
            const autoCloseTime = this.isMobileDevice() ? 20000 : 15000; // Longer on mobile
            setTimeout(() => {
                desktopNotification.close();
            }, autoCloseTime);

            console.log('✅ Enhanced desktop notification created successfully');

        } catch (error) {
            console.error('❌ Error creating desktop notification:', error);
        }
    }

    /**
     * Check if device is mobile
     */
    isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Handle mobile notification click
     */
    handleMobileNotificationClick(notification) {
        // Try to open in app if it's a PWA
        if (window.matchMedia('(display-mode: standalone)').matches) {
            console.log('📱 App is in standalone mode');
            // Already in app, just focus
            window.focus();
        } else {
            console.log('📱 Opening in browser');
            // Open in browser
            window.focus();
        }
    }

    /**
     * Play mobile notification sound
     */
    playMobileNotificationSound() {
        try {
            // Create audio context for mobile sound
            if ('AudioContext' in window || 'webkitAudioContext' in window) {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            }
        } catch (error) {
            console.log('🔊 Mobile sound not available:', error);
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

    /**
     * Check Firebase notification status (Firebase handled in app.blade.php)
     */
    async checkFirebaseStatus() {
        try {
            const response = await fetch('/api/notifications/status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('📱 Firebase status:', data);
                return data;
            } else {
                console.error('❌ Failed to get Firebase status:', response.status);
                return { success: false, isSubscribed: false };
            }
            
        } catch (error) {
            console.error('❌ Error getting Firebase status:', error);
            return { success: false, isSubscribed: false };
        }
    }

    /**
     * Send Firebase push notification for visit assignment - DISABLED FOR UNIFIED SYSTEM
     */
    async sendFirebaseNotification(title, body, data = {}) {
        console.log('🚀 Firebase notification sending DISABLED - using unified backend system');
        console.log('📨 Title:', title);
        console.log('📨 Body:', body);
        console.log('📨 Data:', data);
        
        // DISABLED: This was causing duplicate notifications
        // Firebase notifications are now sent directly from the backend
        // when assignments are created, providing unified PWA-style notifications
        
        return true; // Return success to prevent errors
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
