

function sendNotification(type, title, description, duration = 5000) {
    // check if container exists
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