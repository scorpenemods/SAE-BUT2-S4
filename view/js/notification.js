function sendNotification(type, title, description, duration = 5000) {
    let container = document.getElementById("notification-container")
    if (!container) {
        container = document.createElement('div');
        container.setAttribute("id", 'notification-container');
        document.body.prepend(container);
    }

    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-title">${title}</div>
        <div class="notification-description">${description}</div>
      `;
    container.appendChild(notification);

    setTimeout(() => notification.remove(), duration);
}

function parseNotification() {
    const params = new URLSearchParams(window.location.search);
    const notification = params.get('notification');

    console.log(notification);

    if (notification) {
        const [type, title, body] = notification.split('/');

        return {
            type: decodeURIComponent(type),
            title: decodeURIComponent(title),
            body: decodeURIComponent(body)
        }
    }

    return null;
}

document.addEventListener('DOMContentLoaded', function() {
    const notification = parseNotification();

    if (notification) {
        sendNotification(notification.type, notification.title, notification.body);
    }
});