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
    // Count the actual highlighted notification cards
    const unreadNotifications = document.querySelectorAll(
        '.noti-card.unread'
    ).length;

    const summaryText = document.querySelector('.summary-card p');

    if (!summaryText) {
        return;
    }

    if (unreadNotifications === 0) {
        summaryText.innerHTML =
            'You have <strong>no unread notifications</strong>.';
    } else {
        const label = unreadNotifications === 1
            ? 'notification'
            : 'notifications';

        summaryText.innerHTML =
            `You have <strong>${unreadNotifications} unread ` +
            `${label}</strong>.`;
    }
}

// Count the highlighted cards when the page first loads
document.addEventListener('DOMContentLoaded', updateUnreadCount);



async function viewAppointment(button, tabName) {
    const notification = button.closest('.noti-card');
    const notificationId = notification.dataset.id;
    const allowedTabs = [
        "pending",
        "approved",
        "completed",
        "cancelled"
    ];

    if (!allowedTabs.includes(tabName)) {
        tabName = "pending";
    }

    // Mark unread notifications as read before navigating
    if (notification.classList.contains('unread')) {
        try {
            const response = await fetch('../actions/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'notification_id=' + encodeURIComponent(notificationId)
            });

            const data = await response.json();

            if (data.success) {
                notification.classList.remove('unread');

                const unreadDot = notification.querySelector('.unread-dot');
                if (unreadDot) {
                    unreadDot.remove();
                }

                const markReadButton =
                    notification.querySelector('.btn-noti-secondary');

                if (markReadButton) {
                    markReadButton.remove();
                }

                updateUnreadCount();
            } else {
                console.error('Could not mark notification as read.');
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    window.location.href =
        `view_appointments.php?tab=${encodeURIComponent(tabName)}`;
}

