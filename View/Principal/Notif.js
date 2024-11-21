// Fonction pour basculer l'affichage de la fenêtre popup des notifications
function toggleNotificationPopup() {
    const popup = document.getElementById('notification-popup');
    popup.classList.toggle('visible');

    // Si la popup est maintenant visible, on marque les notifications comme vues et on les charge
    if (popup.classList.contains('visible')) {
        markNotificationsAsSeen();
        loadNotifications();
    }
}

// Fonction pour fermer la fenêtre popup des notifications
function closeNotificationPopup() {
    const popup = document.getElementById('notification-popup');
    popup.classList.remove('visible');
}

// Fonction pour charger les notifications dans la fenêtre popup
// Utilisez le nouveau chemin pour accéder à GetNotifications.php dans le dossier 'notifications'
function loadNotifications() {
    fetch('getNotifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationList = document.getElementById('notification-list');
            notificationList.innerHTML = '';

            if (data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    const listItem = document.createElement('li');
                    listItem.className = `notification-item ${notification.seen ? 'seen' : 'unseen'}`;
                    listItem.innerHTML = `
                        <strong>${notification.type}</strong>
                        <p>${notification.content}</p>
                        <small>${notification.created_at}</small>
                    `;
                    notificationList.appendChild(listItem);
                });
            } else {
                notificationList.innerHTML = '<li>Aucune notification.</li>';
            }
        })
        .catch(error => console.error('Erreur lors du chargement des notifications :', error));
}


// Fonction pour vérifier les nouvelles notifications toutes les 10 secondes
function checkNewNotifications() {
    fetch('getUnreadNotificationCount.php')
        .then(response => response.json())
        .then(data => {
            updateNotificationIcon(data.unreadCount);

            // Si de nouvelles notifications sont disponibles
            if (data.newNotifications) {
                displayPopupNotification("Vous avez de nouvelles notifications !");
                loadNotifications(); // Recharger la liste des notifications
            }
        })
        .catch(error => console.error('Erreur lors de la vérification des notifications :', error));
}

// Fonction pour mettre à jour l'icône et le compteur de notifications
function updateNotificationIcon(count) {
    const notificationIconImg = document.getElementById('notification-icon-img');
    const notificationCount = document.getElementById('notification-count');

    if (count > 0) {
        // Si des notifications non lues sont présentes
        notificationIconImg.src = '../Resources/notifpresent.png';
        notificationCount.style.display = 'block';
        notificationCount.textContent = count;
    } else {
        // Si aucune notification non lue
        notificationIconImg.src = '../Resources/Notif.png';
        notificationCount.style.display = 'none';
    }
}

// Fonction pour afficher une notification popup au centre supérieur de l'écran
function displayPopupNotification(message) {
    const notificationPopup = document.createElement('div');
    notificationPopup.classList.add('popup-notification');
    notificationPopup.innerText = message;
    document.body.appendChild(notificationPopup);

    // Supprimer la popup après 5 secondes
    setTimeout(() => {
        notificationPopup.remove();
    }, 5000);
}

// Fonction pour marquer toutes les notifications comme vues
function markNotificationsAsSeen() {
    fetch('markNotificationsAsSeen.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationIcon(0);
            }
        })
        .catch(error => console.error('Erreur lors de la mise à jour des notifications :', error));
}

// Démarrer la vérification des nouvelles notifications toutes les 10 secondes
setInterval(checkNewNotifications, 10000);

// Charger les notifications lorsque la page est chargée
document.addEventListener('DOMContentLoaded', () => {
    checkNewNotifications();
    loadNotifications();
});
