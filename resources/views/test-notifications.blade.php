@extends('layouts.app')

@section('title', 'Test Notifications - Task Book')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i>Notification System Test
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>🔔 Notification Status</h6>
                            <div id="notification-status" class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Checking notification support...
                            </div>
                            
                            <h6>🎯 Test Notifications</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="testBasicNotification()">
                                    <i class="fas fa-bell me-2"></i>Test Basic Notification
                                </button>
                                
                                <button class="btn btn-success" onclick="testVisitAssignment()">
                                    <i class="fas fa-user-plus me-2"></i>Test Visit Assignment
                                </button>
                                
                                <button class="btn btn-warning" onclick="testVoiceAlert()">
                                    <i class="fas fa-volume-up me-2"></i>Test Voice Alert
                                </button>
                                
                                <button class="btn btn-info" onclick="checkNotificationPermission()">
                                    <i class="fas fa-shield-alt me-2"></i>Check Permission
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>📱 Browser Support</h6>
                            <div id="browser-support" class="alert alert-secondary">
                                <i class="fas fa-desktop me-2"></i>
                                Checking browser support...
                            </div>
                            
                            <h6>🔊 Voice Settings</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="voiceEnabled" checked>
                                <label class="form-check-label" for="voiceEnabled">
                                    Enable Voice Alerts
                                </label>
                            </div>
                            
                            <h6>📊 Notification Log</h6>
                            <div id="notification-log" class="border rounded p-3" style="height: 200px; overflow-y: auto; background: #f8f9fa;">
                                <small class="text-muted">Notification events will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Test notification functions
function testBasicNotification() {
    if (window.notificationSystem) {
        window.notificationSystem.testNotification();
        logNotification('Basic notification test triggered');
    } else {
        logNotification('❌ Notification system not initialized', 'error');
    }
}

function testVisitAssignment() {
    // Simulate a visit assignment notification
    const testNotification = {
        type: 'visit_assigned',
        title: 'New Visit Assigned',
        message: 'You have been assigned a new visit: John Doe - Course Inquiry',
        data: {
            interaction_id: 'test_123',
            visitor_name: 'John Doe',
            purpose: 'Course Inquiry',
            assigned_to: '{{ auth()->user()->name }}'
        },
        timestamp: new Date().toISOString()
    };
    
    if (window.notificationSystem) {
        window.notificationSystem.showNotification(testNotification);
        logNotification('Visit assignment notification test triggered');
    } else {
        logNotification('❌ Notification system not initialized', 'error');
    }
}

function testVoiceAlert() {
    if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance();
        utterance.text = 'This is a test voice alert for the notification system.';
        utterance.volume = 0.8;
        utterance.rate = 0.9;
        speechSynthesis.speak(utterance);
        logNotification('Voice alert test triggered');
    } else {
        logNotification('❌ Speech synthesis not supported', 'error');
    }
}

function checkNotificationPermission() {
    if ('Notification' in window) {
        const permission = Notification.permission;
        let status = '';
        let className = '';
        
        switch(permission) {
            case 'granted':
                status = '✅ Notifications are enabled';
                className = 'alert-success';
                break;
            case 'denied':
                status = '❌ Notifications are blocked';
                className = 'alert-danger';
                break;
            case 'default':
                status = '⚠️ Notification permission not requested';
                className = 'alert-warning';
                break;
        }
        
        document.getElementById('notification-status').innerHTML = 
            `<i class="fas fa-shield-alt me-2"></i>${status}`;
        document.getElementById('notification-status').className = `alert ${className}`;
        
        logNotification(`Permission check: ${permission}`);
    } else {
        document.getElementById('notification-status').innerHTML = 
            '<i class="fas fa-times-circle me-2"></i>❌ Notifications not supported';
        document.getElementById('notification-status').className = 'alert alert-danger';
        logNotification('❌ Notifications not supported', 'error');
    }
}

function logNotification(message, type = 'info') {
    const log = document.getElementById('notification-log');
    const timestamp = new Date().toLocaleTimeString();
    const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
    
    const logEntry = document.createElement('div');
    logEntry.innerHTML = `<small>${icon} [${timestamp}] ${message}</small>`;
    logEntry.className = `mb-1 ${type === 'error' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-info'}`;
    
    log.appendChild(logEntry);
    log.scrollTop = log.scrollHeight;
}

// Check browser support
function checkBrowserSupport() {
    const support = {
        notifications: 'Notification' in window,
        speechSynthesis: 'speechSynthesis' in window,
        serviceWorker: 'serviceWorker' in navigator
    };
    
    let supportText = '';
    let supportClass = '';
    
    if (support.notifications && support.speechSynthesis) {
        supportText = '✅ Full support - All features available';
        supportClass = 'alert-success';
    } else if (support.notifications) {
        supportText = '⚠️ Partial support - Voice alerts may not work';
        supportClass = 'alert-warning';
    } else {
        supportText = '❌ Limited support - Basic notifications only';
        supportClass = 'alert-danger';
    }
    
    document.getElementById('browser-support').innerHTML = 
        `<i class="fas fa-desktop me-2"></i>${supportText}`;
    document.getElementById('browser-support').className = `alert ${supportClass}`;
    
    // Log detailed support info
    logNotification(`Browser support: Notifications=${support.notifications}, Speech=${support.speechSynthesis}, ServiceWorker=${support.serviceWorker}`);
}

// Voice toggle
document.getElementById('voiceEnabled').addEventListener('change', function() {
    if (window.notificationSystem) {
        window.notificationSystem.toggleVoice(this.checked);
        logNotification(`Voice alerts ${this.checked ? 'enabled' : 'disabled'}`);
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    checkBrowserSupport();
    checkNotificationPermission();
    
    // Check if notification system is initialized
    setTimeout(() => {
        if (window.notificationSystem) {
            logNotification('✅ Notification system initialized successfully', 'success');
        } else {
            logNotification('❌ Notification system failed to initialize', 'error');
        }
    }, 1000);
});
</script>
@endsection
