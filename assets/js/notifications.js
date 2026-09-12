function openLogoutModal() {
    document.getElementById('logoutModal').classList.remove('hidden');
}

function closeLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
}

function confirmLogout() {
    window.location.href = "../auth/logout.php";
}

// MARK AS READ FUNCTIONALITY
function markAsRead(button) {
    const notification = button.closest('.noti-card');
    const notificationId = notification.dataset.id;

    fetch('../actions/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'notification_id=' + encodeURIComponent(notificationId)
    })
    .then(response => response.json())
    .then(data => {

        if (data.success) {

            notification.classList.remove('unread');

            const unreadDot = notification.querySelector('.unread-dot');

            if (unreadDot) {
                unreadDot.remove();
            }

            button.remove();

            updateUnreadCount();
        }

    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function updateUnreadCount() {
    const unreadNotifications = document.querySelectorAll('.noti-card.unread').length;

    const summaryText = document.querySelector('.summary-card p');

    if (unreadNotifications === 0) {
        summaryText.innerHTML = 'You have <strong>no unread</strong> notifications today.';
    } else {
        summaryText.innerHTML = `You have <strong>${unreadNotifications} unread</strong> notifications today.`;
    }
}

function viewAppointment() {
    window.location.href = 'view_appointments.html';
}
