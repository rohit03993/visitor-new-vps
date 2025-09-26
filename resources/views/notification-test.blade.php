@extends('layouts.app')

@section('title', 'Notification Test - Task Book')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bell me-2"></i>🔔 Notification System Test
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">🎯 Quick Tests</h6>
                            <div class="d-grid gap-2 mb-4">
                                <button class="btn btn-primary btn-lg" onclick="testNotification()">
                                    <i class="fas fa-bell me-2"></i>Test Full Notification
                                </button>
                                
                                <button class="btn btn-success btn-lg" onclick="testVoice()">
                                    <i class="fas fa-volume-up me-2"></i>Test Voice Only
                                </button>
                                
                                <button class="btn btn-info btn-lg" onclick="checkNotificationPermission()">
                                    <i class="fas fa-shield-alt me-2"></i>Check Permissions
                                </button>
                            </div>

                            <h6 class="text-primary">📊 System Status</h6>
                            <div id="system-status" class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Checking system status...
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary">🔧 Console Commands</h6>
                            <div class="alert alert-light">
                                <p><strong>Open browser console (F12) and try these commands:</strong></p>
                                <ul class="mb-0">
                                    <li><code>testNotification()</code> - Test full notification</li>
                                    <li><code>testVoice()</code> - Test voice only</li>
                                    <li><code>checkNotificationPermission()</code> - Check permissions</li>
                                    <li><code>window.notificationSystem</code> - View system object</li>
                                </ul>
                            </div>

                            <h6 class="text-primary">📱 What Should Happen</h6>
                            <div class="alert alert-success">
                                <ul class="mb-0">
                                    <li>✅ <strong>Desktop Notification:</strong> Popup appears</li>
                                    <li>✅ <strong>Voice Alert:</strong> Browser speaks the message</li>
                                    <li>✅ <strong>In-Page Toast:</strong> Notification appears on page</li>
                                    <li>✅ <strong>Console Logs:</strong> Debug info in console</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Check system status
function checkSystemStatus() {
    const status = document.getElementById('system-status');
    let statusText = '';
    let statusClass = '';

    // Check notification system
    if (window.notificationSystem) {
        statusText += '✅ Notification system loaded<br>';
    } else {
        statusText += '❌ Notification system not loaded<br>';
    }

    // Check browser support
    if ('Notification' in window) {
        statusText += '✅ Browser notifications supported<br>';
    } else {
        statusText += '❌ Browser notifications not supported<br>';
    }

    if ('speechSynthesis' in window) {
        statusText += '✅ Voice synthesis supported<br>';
    } else {
        statusText += '❌ Voice synthesis not supported<br>';
    }

    // Check permission
    if ('Notification' in window) {
        const permission = Notification.permission;
        if (permission === 'granted') {
            statusText += '✅ Notification permission granted<br>';
            statusClass = 'alert-success';
        } else if (permission === 'denied') {
            statusText += '❌ Notification permission denied<br>';
            statusClass = 'alert-danger';
        } else {
            statusText += '⚠️ Notification permission not requested<br>';
            statusClass = 'alert-warning';
        }
    }

    status.innerHTML = statusText;
    status.className = `alert ${statusClass}`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(checkSystemStatus, 1000);
});
</script>
@endsection

