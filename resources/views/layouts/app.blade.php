<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>@yield('title', 'LogBook - Create | Manage | Track')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#007bff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="VMS CRM">
    
    <!-- Firebase Web SDK - UNIFIED NOTIFICATION SYSTEM -->
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
        import { getMessaging, getToken, onMessage } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js';
        
// Firebase configuration - Auto-detects environment
const isProduction = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
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
console.log('🌍 Environment:', isProduction ? 'Production' : 'Development');
console.log('🌍 Hostname:', window.location.hostname);
        
        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);
        
        // Make messaging and functions available globally
        window.messaging = messaging;
        window.firebaseMessaging = messaging; // Keep both for compatibility
        window.getToken = getToken;
        window.firebaseInitialized = true;
        
        console.log('✅ Firebase SDK loaded and available globally');
        
        // Request permission and get FCM token
        async function initializeFirebaseNotifications() {
            try {
                // Mobile-specific debugging
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                console.log('📱 Mobile device detected:', isMobile);
                console.log('📱 User Agent:', navigator.userAgent);
                
                // Request permission
                const permission = await Notification.requestPermission();
                console.log('🔔 Firebase notification permission:', permission);
                
                if (permission === 'granted') {
                    // Register Firebase service worker and wait for it to be active
                    console.log('🔧 Registering Service Worker for mobile...');
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    await navigator.serviceWorker.ready;
                    console.log('✅ Service Worker registered and ready');
                    
                    // Get FCM token with our custom service worker
                    const token = await getToken(messaging, {
                        vapidKey: 'BNUSY-e9yHJJq1URqcCsR5dWgv4RecL74SabGdR0T1JLtJnD4GRtDScNcit5A9RDeD0XOpGpkf_V3VXiPkV9XS8',
                        serviceWorkerRegistration: registration
                    });
                    
                    if (token) {
                        console.log('✅ Firebase FCM token obtained:', token.substring(0, 20) + '...');
                        console.log('📱 Mobile FCM token length:', token.length);
                        
                        // Store FCM token in session
                        try {
                            const response = await fetch('/api/notifications/store-fcm-token', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({ fcm_token: token })
                            });
                            
                            const result = await response.json();
                            console.log('📱 Mobile FCM token storage result:', result);
                            
                            if (result.success) {
                                console.log('✅ Mobile FCM token stored successfully');
                            } else {
                                console.error('❌ Mobile FCM token storage failed:', result.message);
                            }
                        } catch (error) {
                            console.error('❌ Mobile FCM token storage error:', error);
                        }
                    } else {
                        console.log('❌ No FCM token received on mobile');
                    }
                } else {
                    console.log('❌ Notification permission denied');
                }
            } catch (error) {
                console.error('❌ Firebase initialization error:', error);
            }
        }
        
        // Listen for foreground messages and display them
        onMessage(messaging, (payload) => {
            console.log('📨 Firebase foreground message received:', payload);
            
            // Show notification for foreground messages (when browser tab is active)
            if (payload.data && payload.data.source === 'unified_notification') {
                const notificationTitle = payload.notification?.title || payload.data?.title || 'VMS CRM';
                const notificationOptions = {
                    body: payload.notification?.body || payload.data?.body || 'You have a new notification',
                    icon: '/favicon.svg',
                    badge: '/favicon.svg',
                    tag: 'vms-notification',
                    requireInteraction: false,
                    silent: false,
                    data: payload.data || {},
                    timestamp: Date.now()
                };
                
                console.log('🔔 Showing foreground notification:', notificationTitle);
                
                // Show the notification
                if (Notification.permission === 'granted') {
                    const notification = new Notification(notificationTitle, notificationOptions);
                    
                    notification.onclick = function() {
                        console.log('🔔 Foreground notification clicked');
                        window.focus();
                        notification.close();
                    };
                    
                    // Auto-close after 5 seconds
                    setTimeout(() => {
                        notification.close();
                    }, 5000);
                }
            }
        });
        
        // Initialize Firebase notifications when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeFirebaseNotifications);
        } else {
            initializeFirebaseNotifications();
        }
        
        // Make initializeFirebaseNotifications globally available
        window.initializeFirebaseNotifications = initializeFirebaseNotifications;
    </script> <!-- FIREBASE SDK ENABLED FOR UNIFIED NOTIFICATIONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/paytm-theme.css') }}?v={{ filemtime(public_path('css/paytm-theme.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/notifications.css') }}?v={{ filemtime(public_path('css/notifications.css')) }}" rel="stylesheet">
    <style>
        /* Mobile-first responsive design */
        body {
            font-size: 14px;
            line-height: 1.4;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
            color: white;
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            z-index: 1050;
            transition: left 0.3s ease;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar nav {
            flex: 1;
            padding-bottom: 80px; /* Space for footer */
        }
        
        .sidebar-footer {
            margin-top: auto;
        }
        
        /* Logo Container - Professional styling */
        .logo-container {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
            max-width: 180px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .logo-container:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .company-logo {
            width: 100%;
            height: auto;
            max-height: 80px;
            object-fit: contain;
            display: block;
        }
        
        .sidebar.show {
            left: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        /* Settings dropdown styling */
        .sidebar .dropdown-menu {
            background-color: #1a1d29;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
        }
        
        .sidebar .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .sidebar .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .sidebar .dropdown-item.active {
            background-color: #007bff;
            color: #fff;
        }
        
        .sidebar .dropdown-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 0.5rem 0;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.sidebar-open {
            margin-left: 280px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 1rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            border: none;
            padding: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
            border: none;
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 16px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px var(--paytm-primary-shadow-hover);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
            color: white;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 12px;
            opacity: 0.9;
        }
        
        /* Mobile navigation */
        .mobile-nav {
            background: linear-gradient(135deg, var(--paytm-primary) 0%, var(--paytm-primary-dark) 100%);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        
        .mobile-menu-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 0.5rem;
        }
        
        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .mobile-user-avatar {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            display: none;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        /* Responsive tables */
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table {
            font-size: 13px;
            margin-bottom: 0;
        }
        
        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            padding: 0.75rem 0.5rem;
        }
        
        .table td {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }
        
        /* Badge adjustments */
        .badge {
            font-size: 11px;
            padding: 0.4em 0.6em;
        }
        
        /* Form adjustments */
        .form-control, .form-select {
            font-size: 14px;
            padding: 0.75rem;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 14px;
        }
        
        /* Modal adjustments */
        .modal-dialog {
            margin: 1rem;
        }
        
        .modal-content {
            border-radius: 15px;
        }
        
        .modal-header {
            border-radius: 15px 15px 0 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        /* Progress bars */
        .progress {
            height: 15px;
            border-radius: 10px;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
        
        /* Tablet and desktop adjustments */
        @media (min-width: 768px) {
            body {
                font-size: 16px;
            }
            
            .sidebar {
                position: relative !important;
                left: 0 !important;
                width: auto;
                transform: none !important;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-nav {
                display: none;
            }
            
            .stats-number {
                font-size: 2.5rem;
            }
            
            .stats-label {
                font-size: 14px;
            }
            
            .table {
                font-size: 14px;
            }
            
            .table th,
            .table td {
                padding: 0.75rem;
            }
            
            .badge {
                font-size: 12px;
            }
        }
        
        @media (min-width: 992px) {
            .main-content {
                margin-left: 0;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .stats-card {
                padding: 1.5rem;
            }
        }
        
        /* Touch-friendly buttons */
        @media (max-width: 767px) {
            .btn {
                min-height: 44px;
                padding: 0.75rem 1rem;
                font-size: 14px;
            }
            
            .btn-sm {
                min-height: 36px;
                padding: 0.5rem 0.75rem;
                font-size: 12px;
            }
            
            .nav-link {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
        
        /* Custom Pagination Styles */
        .pagination {
            --bs-pagination-padding-x: 0.75rem;
            --bs-pagination-padding-y: 0.375rem;
            --bs-pagination-font-size: 0.875rem;
            --bs-pagination-color: #6c757d;
            --bs-pagination-bg: #fff;
            --bs-pagination-border-width: 1px;
            --bs-pagination-border-color: #dee2e6;
            --bs-pagination-border-radius: 0.375rem;
            --bs-pagination-hover-color: #495057;
            --bs-pagination-hover-bg: #e9ecef;
            --bs-pagination-hover-border-color: #dee2e6;
            --bs-pagination-focus-color: #495057;
            --bs-pagination-focus-bg: #e9ecef;
            --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            --bs-pagination-active-color: #fff;
            --bs-pagination-active-bg: #0d6efd;
            --bs-pagination-active-border-color: #0d6efd;
            --bs-pagination-disabled-color: #6c757d;
            --bs-pagination-disabled-bg: #fff;
            --bs-pagination-disabled-border-color: #dee2e6;
        }
        
        .page-link {
            border: 1px solid #dee2e6;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        
        .page-link:hover {
            color: #495057;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.5;
        }
        
        /* Mobile-friendly pagination */
        @media (max-width: 576px) {
            .pagination {
                --bs-pagination-padding-x: 0.5rem;
                --bs-pagination-padding-y: 0.25rem;
                --bs-pagination-font-size: 0.8rem;
            }
            
            .page-link {
                min-width: 40px;
                text-align: center;
            }
        }
        
        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }
            
            .print-content, .print-content * {
                visibility: visible;
            }
            
            .print-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            .sidebar, .mobile-nav, .btn, .modal, .pagination, .card-header {
                display: none !important;
            }
            
            .table {
                font-size: 12px;
                border-collapse: collapse;
            }
            
            .table th, .table td {
                border: 1px solid #000;
                padding: 4px;
            }
            
            .badge {
                border: 1px solid #000;
                background: none !important;
                color: #000 !important;
            }
            
            .print-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            
            .print-footer {
                margin-top: 20px;
                text-align: center;
                font-size: 10px;
                border-top: 1px solid #000;
                padding-top: 10px;
            }
        }
        
        /* Global Remark Text Wrapping - Applied across all pages */
        .remark-text {
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: break-word;
            line-height: 1.4;
            font-size: 0.875rem;
            width: 100%;
            display: block;
        }
        
        @media (max-width: 767px) {
            .remark-text {
                font-size: 0.8rem;
                padding: 0.5rem !important;
                line-height: 1.3;
            }
        }

        /* Global Pagination Styles */
        .pagination {
            --bs-pagination-padding-x: 0.5rem;
            --bs-pagination-padding-y: 0.375rem;
            --bs-pagination-font-size: 0.875rem;
            --bs-pagination-color: #6c757d;
            --bs-pagination-bg: #fff;
            --bs-pagination-border-width: 1px;
            --bs-pagination-border-color: #dee2e6;
            --bs-pagination-border-radius: 0.375rem;
            --bs-pagination-hover-color: #495057;
            --bs-pagination-hover-bg: #e9ecef;
            --bs-pagination-hover-border-color: #dee2e6;
            --bs-pagination-focus-color: #495057;
            --bs-pagination-focus-bg: #e9ecef;
            --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            --bs-pagination-active-color: #fff;
            --bs-pagination-active-bg: #0d6efd;
            --bs-pagination-active-border-color: #0d6efd;
            --bs-pagination-disabled-color: #6c757d;
            --bs-pagination-disabled-bg: #fff;
            --bs-pagination-disabled-border-color: #dee2e6;
        }

        .pagination .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            margin: 0 2px;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-radius: 0.375rem;
        }

        /* Make pagination arrows smaller and more subtle */
        .pagination .page-link i {
            font-size: 0.75rem;
        }

        /* Mobile pagination adjustments */
        @media (max-width: 768px) {
            .pagination {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .pagination .page-link {
                padding: 0.5rem 0.75rem;
                margin: 2px;
                min-width: 40px;
                text-align: center;
            }
        }
    </style>
</head>
<body class="{{ auth()->check() ? 'authenticated' : '' }}">
    <!-- Mobile Navigation -->
    <div class="mobile-nav d-md-none">
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-user-info">
            <div class="mobile-user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 600;">{{ auth()->user()->name }}</div>
                <div style="font-size: 12px; opacity: 0.8;">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar" id="sidebar">
                    <div class="p-3">
                        <!-- Company Logo -->
                        <div class="text-center mb-4">
                            <div class="logo-container">
                                <img src="{{ \App\Models\Setting::logo() }}" alt="Company Logo" class="company-logo">
                            </div>
                        </div>
                        
                        <div class="text-center mb-4 d-none d-md-block">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div class="mt-2">
                                <strong>{{ auth()->user()->name }}</strong><br>
                                <small class="text-light">{{ ucfirst(auth()->user()->role) }}</small>
                            </div>
                        </div>
                    </div>
                    
                    <nav class="nav flex-column px-3">
                        @if(auth()->user()->isAdmin())
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" onclick="closeSidebar()">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.search-mobile') ? 'active' : '' }}" href="{{ route('admin.search-mobile') }}" onclick="closeSidebar()">
                                <i class="fas fa-search me-2"></i> Search Log
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.manage-users') ? 'active' : '' }}" href="{{ route('admin.manage-users') }}" onclick="closeSidebar()">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.manage-locations') ? 'active' : '' }}" href="{{ route('admin.manage-locations') }}" onclick="closeSidebar()">
                                <i class="fas fa-map-marker-alt me-2"></i> Manage Locations
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.manage-branches') ? 'active' : '' }}" href="{{ route('admin.manage-branches') }}" onclick="closeSidebar()">
                                <i class="fas fa-building me-2"></i> Manage Branches
                            </a>
                <a class="nav-link {{ request()->routeIs('admin.manage-tags') ? 'active' : '' }}" href="{{ route('admin.manage-tags') }}" onclick="closeSidebar()">
                    <i class="fas fa-tags me-2"></i> Edit Purpose
                </a>
                <a class="nav-link {{ request()->routeIs('admin.manage-courses') ? 'active' : '' }}" href="{{ route('admin.manage-courses') }}" onclick="closeSidebar()">
                    <i class="fas fa-graduation-cap me-2"></i> Manage Courses
                </a>
                            <a class="nav-link {{ request()->routeIs('admin.filter-visitors') ? 'active' : '' }}" href="{{ route('admin.filter-visitors') }}" onclick="closeSidebar()">
                                <i class="fas fa-filter me-2"></i> Filter Visitors
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.filter-interactions') ? 'active' : '' }}" href="{{ route('admin.filter-interactions') }}" onclick="closeSidebar()">
                                <i class="fas fa-search me-2"></i> Filter Interactions
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.analytics') ? 'active' : '' }}" href="{{ route('admin.analytics') }}" onclick="closeSidebar()">
                                <i class="fas fa-chart-bar me-2"></i> Analytics
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.file-management') ? 'active' : '' }}" href="{{ route('admin.file-management') }}" onclick="closeSidebar()">
                                <i class="fas fa-folder-open me-2"></i> File Management
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}" onclick="closeSidebar()">
                                <i class="fas fa-cog me-2"></i> App Settings
                            </a>
                        @elseif(auth()->user()->isStaff())
                            <a class="nav-link {{ request()->routeIs('staff.visitor-search') ? 'active' : '' }}" href="{{ route('staff.visitor-search') }}" onclick="closeSidebar()">
                                <i class="fas fa-search me-2"></i> Search Log
                            </a>
                            <a class="nav-link {{ request()->routeIs('staff.assigned-to-me') ? 'active' : '' }}" href="{{ route('staff.assigned-to-me') }}" onclick="closeSidebar()">
                                <i class="fas fa-user-check me-2"></i> Assigned Logs
                            </a>
                            <a class="nav-link {{ request()->routeIs('staff.notifications-dashboard') ? 'active' : '' }}" href="{{ route('staff.notifications-dashboard') }}" onclick="closeSidebar()">
                                <i class="fas fa-bell me-2"></i> Notifications
                                <span class="badge bg-danger ms-2" id="sidebarNotificationCount" style="display: none;">0</span>
                            </a>
                            
                            <!-- Settings Dropdown -->
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog me-2"></i> Settings
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li>
                                        <button class="dropdown-item" id="pushNotificationBtn" onclick="togglePushNotifications()">
                                            <i class="fas fa-wifi me-2"></i> <span id="pushNotificationText">Enable Push</span>
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('staff.change-password') ? 'active' : '' }}" href="{{ route('staff.change-password') }}" onclick="closeSidebar()">
                                <i class="fas fa-key me-2"></i> Change Password
                            </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        
                        <hr class="my-3">
                        
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link text-start w-100 p-0">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </nav>
                    
                    <!-- Powered By Footer -->
                    <div class="sidebar-footer text-center p-3 mt-auto" style="border-top: 1px solid rgba(255,255,255,0.2);">
                        <small style="color: rgba(255,255,255,0.7); font-size: 0.75rem;">
                            Powered by <strong style="color: white;">TaskBook</strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content" id="mainContent">
                    <!-- Top Navigation (Desktop) -->
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm d-none d-md-block">
                        <div class="container-fluid">
                            <div class="navbar-nav ms-auto">
                                <span class="navbar-text">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ \App\Helpers\DateTimeHelper::formatIndianDate(now()) }}
                                </span>
                            </div>
                        </div>
                    </nav>

                    <!-- Page Content -->
                    <div class="container-fluid p-3 p-md-4">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            mainContent.classList.toggle('sidebar-open');
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const mainContent = document.getElementById('mainContent');
            
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            mainContent.classList.remove('sidebar-open');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth < 768 && 
                !sidebar.contains(event.target) && 
                !menuBtn.contains(event.target) && 
                sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeSidebar();
            }
        });
    </script>
    
    <!-- Push Notification Subscription Handler -->
    <script>
        async function togglePushNotifications() {
            const btn = document.getElementById('pushNotificationBtn');
            const text = document.getElementById('pushNotificationText');
            
            try {
                // Check if notifications are supported
                if (!('Notification' in window)) {
                    alert('This browser does not support notifications.');
                    return;
                }
                
                // Check if Service Worker is supported
                if (!('serviceWorker' in navigator)) {
                    alert('This browser does not support Service Workers.');
                    return;
                }
                
                // Request notification permission
                const permission = await Notification.requestPermission();
                
                if (permission === 'granted') {
                    // Initialize Firebase notifications using the global function
                    if (window.initializeFirebaseNotifications) {
                        try {
                            await window.initializeFirebaseNotifications();
                            
                            // Get FCM token with our custom Service Worker
                            const registration = await navigator.serviceWorker.register('/sw.js');
                            await navigator.serviceWorker.ready;
                            
                            const token = await window.getToken(window.firebaseMessaging, {
                                vapidKey: 'BNUSY-e9yHJJq1URqcCsR5dWgv4RecL74SabGdR0T1JLtJnD4GRtDScNcit5A9RDeD0XOpGpkf_V3VXiPkV9XS8',
                                serviceWorkerRegistration: registration
                            });
                            
                            if (token) {
                                // Store FCM token in session
                                const storeResponse = await fetch('/api/notifications/store-fcm-token', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({ fcm_token: token })
                                });
                                
                                if (storeResponse.ok) {
                                    // Update button text
                                    text.textContent = 'Firebase Notifications ✅';
                                    btn.style.color = '#28a745';
                                    
                                    // Show success message
                                    alert('🔔 Firebase notifications enabled! You will now receive native desktop notifications when logs are assigned to you.');
                                } else {
                                    alert('Failed to store FCM token. Please refresh the page and try again.');
                                }
                            } else {
                                alert('Failed to get FCM token. Please refresh the page and try again.');
                            }
                        } catch (error) {
                            console.error('Firebase notification error:', error);
                            alert('Error setting up Firebase notifications: ' + error.message);
                        }
                    } else {
                        console.error('Firebase SDK not available:', {
                            firebaseInitialized: window.firebaseInitialized,
                            firebaseMessaging: !!window.firebaseMessaging,
                            getToken: !!window.getToken
                        });
                        alert('Firebase SDK not loaded. Please refresh the page and try again.');
                    }
                } else if (permission === 'denied') {
                    alert('❌ Notifications blocked. Please enable notifications in your browser settings.');
                    text.textContent = 'Enable Notifications (Blocked)';
                    btn.style.color = '#dc3545';
                } else {
                    alert('Notifications permission was dismissed.');
                }
            } catch (error) {
                console.error('Push notification error:', error);
                alert('Error setting up notifications: ' + error.message);
            }
        }
        
        // Check Firebase notification status on page load
        document.addEventListener('DOMContentLoaded', async function() {
            const text = document.getElementById('pushNotificationText');
            const btn = document.getElementById('pushNotificationBtn');
            
            if (text && btn) {
                // Check Firebase notification status
                try {
                    const response = await fetch('/api/notifications/status', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.isSubscribed && data.fcm_token) {
                        text.textContent = 'Firebase Notifications ✅';
                        btn.style.color = '#28a745';
                        console.log('✅ Firebase notifications enabled in sidebar');
                    } else {
                        text.textContent = 'Enable Firebase Notifications';
                        btn.style.color = '';
                        console.log('❌ Firebase notifications not enabled in sidebar');
                    }
                } catch (error) {
                    console.error('Error checking Firebase status:', error);
                    text.textContent = 'Enable Notifications';
                    btn.style.color = '';
                }
            }
        });
    </script>
    
    <!-- Notification System -->
    <!-- NOTIFICATIONS.JS COMPLETELY REMOVED FOR DEBUGGING -->
    
    <!-- Debug Notification System -->
    <script>
        console.log('🔍 DEBUG: Checking notification system...');
        console.log('🔍 Body classes:', document.body.className);
        console.log('🔍 Auth check:', {{ auth()->check() ? 'true' : 'false' }});
        
        setTimeout(() => {
            console.log('🔍 Checking Firebase notification system...');
            if (window.firebase && window.messaging) {
                console.log('✅ Firebase notification system is loaded');
                console.log('🔍 Permission:', Notification.permission);
                console.log('🔍 Browser support:', 'Notification' in window);
                console.log('🔍 Firebase messaging available:', !!window.messaging);
            } else {
                console.log('❌ Firebase notification system NOT loaded');
            }
            
            // Check Service Worker status
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration().then(registration => {
                    console.log('🔍 Service Worker registration:', registration);
                    if (registration) {
                        console.log('✅ Service Worker is registered:', registration.scope);
                        console.log('🔍 Service Worker state:', registration.active ? registration.active.state : 'No active worker');
                        
                        // Test Service Worker notification
                        if (registration.active) {
                            console.log('🧪 Testing Service Worker notification...');
                            registration.active.postMessage({ action: 'test-notification' });
                        }
                    } else {
                        console.log('❌ No Service Worker registered');
                    }
                });
            }
        }, 2000);

        // Update notification count in sidebar
        function updateNotificationCount() {
            fetch('/staff/notifications/count')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.unread_count > 0) {
                        const badge = document.getElementById('sidebarNotificationCount');
                        if (badge) {
                            badge.textContent = data.unread_count;
                            badge.style.display = 'inline-block';
                        }
                    }
                })
                .catch(error => console.error('Error fetching notification count:', error));
        }

        // Update notification count on page load and every 30 seconds
        if (document.getElementById('sidebarNotificationCount')) {
            updateNotificationCount();
            setInterval(updateNotificationCount, 30000);
        }
    </script>
    
    @yield('scripts')
</body>
</html>
