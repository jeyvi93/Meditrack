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


/* LIVE NOTIFICATION UPDATES */

let latestNotificationId = 0;

function getLatestNotificationId() {
    const cards = document.querySelectorAll(
        '.notification-feed .noti-card[data-id]'
    );

    let latestId = 0;

    cards.forEach(card => {
        const id = Number(card.dataset.id);

        if (id > latestId) {
            latestId = id;
        }
    });

    return latestId;
}

function getNotificationIcon(title) {
    switch (title) {
        case 'Appointment Approved':
            return { icon: 'bx-check-double', color: 'icon-green' };
        case 'Appointment Rejected':
            return { icon: 'bx-x-circle', color: 'icon-red' };
        case 'Appointment Cancelled':
            return { icon: 'bx-calendar-x', color: 'icon-red' };
        case 'Appointment Completed':
            return { icon: 'bx-check-circle', color: 'icon-green' };
        case 'Appointment Rescheduled':
            return { icon: 'bx-calendar-edit', color: 'icon-blue' };
        default:
            return { icon: 'bx-bell', color: 'icon-green' };
    }
}

function getNotificationTab(title) {
    switch (title) {
        case 'Appointment Approved':
        case 'Appointment Rescheduled':
            return 'approved';
        case 'Appointment Rejected':
        case 'Appointment Cancelled':
            return 'cancelled';
        case 'Appointment Completed':
            return 'completed';
        default:
            return 'pending';
    }
}

function escapeNotificationText(value) {
    const element = document.createElement('div');
    element.textContent = value ?? '';
    return element.innerHTML;
}

function createNotificationCard(notification) {
    const iconInfo = getNotificationIcon(notification.title);
    const card = document.createElement('div');

    card.className = Number(notification.is_read) === 0
        ? 'noti-card unread'
        : 'noti-card';

    card.dataset.id = notification.id;

    const unreadDot = Number(notification.is_read) === 0
        ? '<div class="unread-dot"></div>'
        : '';

    const title = escapeNotificationText(notification.title);
    const message = escapeNotificationText(notification.message);
    const icon = iconInfo.icon;
    const color = iconInfo.color;
    const tab = getNotificationTab(notification.title);
    const time = new Date(
        String(notification.created_at).replace(' ', 'T')
    ).toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit'
    });

    let messageHtml = `<p class="noti-text">${message}</p>`;

    if (
        notification.title === 'Appointment Rejected' ||
        notification.title === 'Appointment Cancelled'
    ) {
        const parts = String(notification.message ?? '').split(' Reason: ');
        const mainMessage = parts[0] ?? '';
        const reasonAndNotes = parts[1] ?? '';
        const reasonParts = reasonAndNotes.split(' Notes: ');
        const reason = reasonParts[0] ?? '';
        const notes = reasonParts[1] ?? '';

        messageHtml = `
            <div class="noti-text">
                <p>${escapeNotificationText(mainMessage)}</p>
                ${reason ? `<p class="noti-detail"><strong>Reason:</strong> ${escapeNotificationText(reason)}</p>` : ''}
                ${notes ? `<p class="noti-detail"><strong>Notes:</strong> ${escapeNotificationText(notes)}</p>` : ''}
            </div>
        `;
    }

    card.innerHTML = `
        ${unreadDot}
        <div class="noti-icon-box ${color}">
            <i class="bx ${icon}"></i>
        </div>
        <div class="noti-content">
            <div class="noti-meta">
                <h4>${title}</h4>
                <span class="noti-time">${time}</span>
            </div>
            ${messageHtml}
            <div class="noti-actions">
                <button class="btn-noti-primary"
                    onclick="viewAppointment(this, '${tab}')">
                    View Appointment
                </button>
                ${Number(notification.is_read) === 0
                    ? '<button class="btn-noti-secondary" onclick="markAsRead(this)">Mark as Read</button>'
                    : ''}
            </div>
        </div>
    `;

    return card;
}

function updateDateDividers() {
    const feed = document.querySelector('.notification-feed');

    if (!feed) return;

    feed.querySelectorAll('.date-divider').forEach(divider => {
        divider.remove();
    });

    let previousDate = null;

    feed.querySelectorAll('.noti-card[data-id]').forEach(card => {
        const notification = card.dataset.createdAt;

        if (!notification) return;

        const date = new Date(notification.replace(' ', 'T'));
        const dateKey = [
            date.getFullYear(),
            String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')
        ].join('-');

        if (dateKey !== previousDate) {
            let label;

            const today = new Date();
            const yesterday = new Date();
            yesterday.setDate(today.getDate() - 1);

            const todayKey = [
                today.getFullYear(),
                String(today.getMonth() + 1).padStart(2, '0'),
                String(today.getDate()).padStart(2, '0')
            ].join('-');

            const yesterdayKey = [
                yesterday.getFullYear(),
                String(yesterday.getMonth() + 1).padStart(2, '0'),
                String(yesterday.getDate()).padStart(2, '0')
            ].join('-');

            if (dateKey === todayKey) {
                label = 'Today';
            } else if (dateKey === yesterdayKey) {
                label = 'Yesterday';
            } else {
                label = date.toLocaleDateString([], {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
            }

            const divider = document.createElement('div');
            divider.className = 'date-divider';
            divider.textContent = label;
            feed.insertBefore(divider, card);
            previousDate = dateKey;
        }
    });
}

async function refreshNotificationFeed() {
    const feed = document.querySelector('.notification-feed');

    if (!feed) return;

    try {
        const response = await fetch('check_notifications.php', {
            cache: 'no-store'
        });

        if (!response.ok) return;

        const data = await response.json();

        if (!data.success || !Array.isArray(data.notifications)) return;

        const existingIds = new Set(
            Array.from(feed.querySelectorAll('.noti-card[data-id]'))
                .map(card => String(card.dataset.id))
        );

        data.notifications.forEach(notification => {
            if (existingIds.has(String(notification.id))) return;

            const card = createNotificationCard(notification);
            card.dataset.createdAt = notification.created_at;

            const firstCard = feed.querySelector('.noti-card[data-id]');

            if (firstCard) {
                feed.insertBefore(card, firstCard);
            } else {
                feed.innerHTML = '';
                feed.appendChild(card);
            }

            existingIds.add(String(notification.id));
        });

        // Ensure existing cards have timestamps for date grouping.
        feed.querySelectorAll('.noti-card[data-id]').forEach(card => {
            const notification = data.notifications.find(
                item => String(item.id) === String(card.dataset.id)
            );

            if (notification) {
                card.dataset.createdAt = notification.created_at;
            }
        });

        updateDateDividers();
        updateUnreadCount();

        if (window.lucide) {
            lucide.createIcons();
        }
    } catch (error) {
        console.error('Live notification update failed:', error);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Keep existing notifications and check for new ones every 5 seconds.
    refreshNotificationFeed();
    setInterval(refreshNotificationFeed, 5000);
});
