@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-bell me-2"></i>My Notifications
                    <span class="badge bg-primary ms-2" id="totalNotificationsCount">0</span>
                </h2>
                <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-1"></i>Mark All as Read
                </button>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4" id="notificationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        <i class="fas fa-list me-1"></i>All
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread" type="button" role="tab">
                        <i class="fas fa-envelope me-1"></i>Unread
                        <span class="badge bg-danger ms-1" id="unreadCount">0</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="read-tab" data-bs-toggle="tab" data-bs-target="#read" type="button" role="tab">
                        <i class="fas fa-envelope-open me-1"></i>Read
                    </button>
                </li>
            </ul>

            <!-- Notifications List -->
            <div class="tab-content" id="notificationTabContent">
                <!-- All Notifications -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    <div id="allNotificationsList" class="notifications-list">
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                            <p class="mt-3 text-muted">Loading notifications...</p>
                        </div>
                    </div>
                </div>

                <!-- Unread Notifications -->
                <div class="tab-pane fade" id="unread" role="tabpanel">
                    <div id="unreadNotificationsList" class="notifications-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Read Notifications -->
                <div class="tab-pane fade" id="read" role="tabpanel">
                    <div id="readNotificationsList" class="notifications-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Notifications Yet</h4>
                <p class="text-muted">You'll see notifications here when there are updates to interactions you're following.</p>
            </div>
        </div>
    </div>
</div>

<style>
.notification-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
    border-left: 4px solid #007bff;
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.notification-card.unread {
    background: #f8f9ff;
    border-left-color: #0d6efd;
}

.notification-card.read {
    background: #f8f9fa;
    border-left-color: #dee2e6;
}

.notification-card.assignment {
    border-left-color: #ffc107;
}

.notification-card.remark {
    border-left-color: #0dcaf0;
}

.notification-card.file_upload {
    border-left-color: #198754;
}

.notification-card.status_change {
    border-left-color: #dc3545;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-right: 15px;
}

.notification-icon.assignment {
    background: #fff3cd;
    color: #ffc107;
}

.notification-icon.remark {
    background: #cff4fc;
    color: #0dcaf0;
}

.notification-icon.file_upload {
    background: #d1e7dd;
    color: #198754;
}

.notification-icon.status_change {
    background: #f8d7da;
    color: #dc3545;
}

.notification-time {
    font-size: 12px;
    color: #6c757d;
}

.notification-message {
    font-size: 14px;
    color: #212529;
    margin-bottom: 5px;
}

.notification-interaction {
    font-size: 13px;
    color: #495057;
    font-weight: 500;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .notification-card {
        padding: 12px;
    }
    
    .notification-icon {
        width: 35px;
        height: 35px;
        font-size: 16px;
        margin-right: 10px;
    }
    
    .notification-message {
        font-size: 13px;
    }
    
    .notification-time {
        font-size: 11px;
    }
}
</style>

<script>
let allNotifications = [];

// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    
    // Auto-refresh every 30 seconds
    setInterval(loadNotifications, 30000);
});

function loadNotifications() {
    fetch('/staff/notifications')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allNotifications = data.notifications;
                updateNotificationCounts(data.unread_count);
                renderNotifications();
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function updateNotificationCounts(unreadCount) {
    document.getElementById('unreadCount').textContent = unreadCount;
    document.getElementById('totalNotificationsCount').textContent = allNotifications.length;
}

function renderNotifications() {
    if (allNotifications.length === 0) {
        document.getElementById('emptyState').style.display = 'block';
        document.getElementById('allNotificationsList').style.display = 'none';
        return;
    }

    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('allNotificationsList').style.display = 'block';

    // Render all notifications
    const allHtml = allNotifications.map(n => createNotificationCard(n)).join('');
    document.getElementById('allNotificationsList').innerHTML = allHtml;

    // Render unread notifications
    const unreadNotifications = allNotifications.filter(n => !n.is_read);
    const unreadHtml = unreadNotifications.length > 0 
        ? unreadNotifications.map(n => createNotificationCard(n)).join('')
        : '<div class="text-center py-5 text-muted">No unread notifications</div>';
    document.getElementById('unreadNotificationsList').innerHTML = unreadHtml;

    // Render read notifications
    const readNotifications = allNotifications.filter(n => n.is_read);
    const readHtml = readNotifications.length > 0
        ? readNotifications.map(n => createNotificationCard(n)).join('')
        : '<div class="text-center py-5 text-muted">No read notifications</div>';
    document.getElementById('readNotificationsList').innerHTML = readHtml;
}

function createNotificationCard(notification) {
    const isRead = notification.is_read ? 'read' : 'unread';
    const icon = getNotificationIcon(notification.notification_type);
    const timeAgo = getTimeAgo(notification.created_at);
    
    return `
        <div class="notification-card ${isRead} ${notification.notification_type}" onclick="markAsReadAndView(${notification.id}, ${notification.interaction_id})">
            <div class="d-flex align-items-start">
                <div class="notification-icon ${notification.notification_type}">
                    <i class="${icon}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">
                        <i class="fas fa-clock me-1"></i>${timeAgo}
                    </div>
                </div>
                ${!notification.is_read ? '<div class="badge bg-primary">New</div>' : ''}
            </div>
        </div>
    `;
}

function getNotificationIcon(type) {
    const icons = {
        'assignment': 'fas fa-exchange-alt',
        'remark': 'fas fa-comment',
        'file_upload': 'fas fa-paperclip',
        'status_change': 'fas fa-check-circle'
    };
    return icons[type] || 'fas fa-bell';
}

function getTimeAgo(datetime) {
    const now = new Date();
    const then = new Date(datetime);
    const diffMs = now - then;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    
    const diffDays = Math.floor(diffHours / 24);
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    
    return then.toLocaleDateString();
}

function markAsReadAndView(notificationId, interactionId) {
    // Mark as read
    fetch('/staff/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            notification_ids: [notificationId]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to visitor profile using interaction ID
            window.location.href = `/staff/interaction/${interactionId}/visitor-profile`;
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        // Even if marking as read fails, still redirect to the interaction
        window.location.href = `/staff/interaction/${interactionId}/visitor-profile`;
    });
}

function markAllAsRead() {
    if (allNotifications.length === 0) {
        return;
    }

    const unreadIds = allNotifications.filter(n => !n.is_read).map(n => n.id);
    
    if (unreadIds.length === 0) {
        alert('All notifications are already read!');
        return;
    }

    fetch('/staff/notifications/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            notification_ids: unreadIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications(); // Reload to show updated state
        }
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
    });
}
</script>
@endsection

