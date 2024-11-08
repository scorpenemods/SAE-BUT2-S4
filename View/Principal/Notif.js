function toggleNotificationPopup() {
    const popup = document.getElementById('notification-popup');
    popup.classList.toggle('visible');
}

function closeNotificationPopup() {
    const popup = document.getElementById('notification-popup');
    popup.classList.remove('visible');
}

// Close the notification popup by clicking outside
document.addEventListener('click', function(event) {
    const popup = document.getElementById('notification-popup');
    const notificationIcon = document.getElementById('notification-icon');

    if (
        event.target !== popup &&
        !popup.contains(event.target) &&
        event.target !== notificationIcon &&
        !notificationIcon.contains(event.target)
    ) {
        popup.classList.remove('visible');
    }
});

