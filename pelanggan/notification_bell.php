<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


<?php
// Get unread notification count
if (isset($_SESSION['account_id'])) {
    try {
        $notifStmt = $pdo->prepare("
            SELECT COUNT(*) as unread_count
            FROM notifications
            WHERE account_id = ? AND is_read = FALSE
        ");
        $notifStmt->execute([$_SESSION['account_id']]);
        $unread = $notifStmt->fetch(PDO::FETCH_ASSOC);
        $unread_count = $unread['unread_count'] ?? 0;
    } catch (PDOException $e) {
        $unread_count = 0;
    }
} else {
    $unread_count = 0;
}
?>

<style>
.notification-bell {
    position: relative;
    cursor: pointer;
    margin-left: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.notification-bell-icon {
    font-size: 24px;
    color: #fff;
    transition: transform 0.3s;
}

.notification-bell:hover .notification-bell-icon {
    transform: scale(1.1);
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    font-size: 11px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    animation: pulse 2s infinite;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.6);
}

@keyframes pulse {
    0%, 100% { 
        transform: scale(1); 
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.6);
    }
    50% { 
        transform: scale(1.15); 
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.8);
    }
}

.notification-dropdown {
    display: none;
    position: absolute;
    top: 45px;
    right: 0;
    width: 380px;
    max-height: 500px;
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
    border: 2px solid #6e22dd;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.8);
    z-index: 9999;
    overflow: hidden;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-dropdown.active {
    display: block;
}

.notification-header {
    background: linear-gradient(135deg, #6e22dd 0%, #5a1bb8 100%);
    padding: 15px 20px;
    font-weight: bold;
    font-size: 16px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-list::-webkit-scrollbar {
    width: 8px;
}

.notification-list::-webkit-scrollbar-track {
    background: #0a0a0a;
}

.notification-list::-webkit-scrollbar-thumb {
    background: #6e22dd;
    border-radius: 4px;
}

.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(110, 34, 221, 0.2);
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.notification-item:hover {
    background: rgba(110, 34, 221, 0.1);
}

.notification-item.unread {
    background: rgba(110, 34, 221, 0.05);
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
    background: #ef4444;
    border-radius: 50%;
}

.notification-title {
    font-weight: bold;
    color: #6e22dd;
    margin-bottom: 5px;
    font-size: 14px;
    padding-left: 10px;
}

.notification-message {
    font-size: 13px;
    color: #ccc;
    line-height: 1.4;
    padding-left: 10px;
}

.notification-time {
    font-size: 11px;
    color: #888;
    margin-top: 5px;
    padding-left: 10px;
}

.notification-footer {
    padding: 12px 20px;
    text-align: center;
    border-top: 2px solid #6e22dd;
    background: rgba(110, 34, 221, 0.05);
}

.notification-footer a {
    color: #6e22dd;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: color 0.3s;
}

.notification-footer a:hover {
    color: #8b4dff;
}

.notification-empty {
    padding: 40px 20px;
    text-align: center;
    color: #888;
}

.notification-empty-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .notification-dropdown {
        width: 320px;
        right: -20px;
    }
}
</style>

<div class="notification-bell" onclick="toggleNotifications(event)">
    <i class="fa-solid fa-bell notification-bell-icon"></i>

    <?php if ($unread_count > 0): ?>
    <span class="notification-badge" id="notificationBadge"><?php echo $unread_count; ?></span>
    <?php endif; ?>
    
    <div id="notificationDropdown" class="notification-dropdown">
        <div class="notification-header">
            Notifications
        </div>
        <div class="notification-list" id="notificationList">
            <div class="notification-empty">
                <div class="notification-empty-icon">⏳</div>
                <div>Loading...</div>
            </div>
        </div>
        <div class="notification-footer">
            <a href="warranty_status.php">View All Claims</a>
        </div>
    </div>
</div>

<script>
let notificationDropdownOpen = false;

function toggleNotifications(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('notificationDropdown');
    
    if (!notificationDropdownOpen) {
        loadNotifications();
        dropdown.classList.add('active');
        notificationDropdownOpen = true;
    } else {
        dropdown.classList.remove('active');
        notificationDropdownOpen = false;
    }
}

function loadNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function displayNotifications(notifications) {
    const listContainer = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        listContainer.innerHTML = `
            <div class="notification-empty">
                <div class="notification-empty-icon">🔕</div>
                <div>No notifications yet</div>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notif => {
        const unreadClass = notif.is_read == '0' ? 'unread' : '';
        const timeAgo = formatTimeAgo(notif.created_at);
        
        html += `
            <div class="notification-item ${unreadClass}" onclick="handleNotificationClick(${notif.notification_id}, '${notif.link_url}')">
                <div class="notification-title">${escapeHtml(notif.title)}</div>
                <div class="notification-message">${escapeHtml(notif.message)}</div>
                <div class="notification-time">${timeAgo}</div>
            </div>
        `;
    });
    
    listContainer.innerHTML = html;
}

function handleNotificationClick(notifId, url) {
    // Mark as read
    fetch('mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: notifId })
    }).then(() => {
        // Update badge count
        updateNotificationBadge();
        // Redirect
        if (url) {
            window.location.href = url;
        }
    });
}

function formatTimeAgo(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffSeconds = Math.floor((now - date) / 1000);
    
    if (diffSeconds < 60) return 'Just now';
    if (diffSeconds < 3600) return Math.floor(diffSeconds / 60) + ' min ago';
    if (diffSeconds < 86400) return Math.floor(diffSeconds / 3600) + ' hrs ago';
    if (diffSeconds < 604800) return Math.floor(diffSeconds / 86400) + ' days ago';
    
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateNotificationBadge() {
    fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            const bell = document.querySelector('.notification-bell');
            
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    // Create badge if doesn't exist
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.id = 'notificationBadge';
                    newBadge.textContent = data.count;
                    bell.appendChild(newBadge);
                }
            } else {
                if (badge) badge.remove();
            }
        });
}

// Auto-refresh badge every 30 seconds
setInterval(updateNotificationBadge, 30000);

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const bell = document.querySelector('.notification-bell');
    const dropdown = document.getElementById('notificationDropdown');
    
    if (!bell.contains(event.target)) {
        dropdown.classList.remove('active');
        notificationDropdownOpen = false;
    }
});
</script>