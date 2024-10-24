// Code menu paramètre
// Afficher
function show(header){
    header.classList.toggle("show-list");
    header.classList.toggle("hide-list");
}
// Cacher
function hide(header){
    header.classList.toggle("hide-list");
    header.classList.toggle("show-list");
}
// Switcher entre les 2 en changeant la classe du body
function toggleMenu() {
    var header = document.querySelector('div.hide-list');
    try { (header.classList.contains("show-list"))
        show(header);
    }
    catch{
        var header = document.querySelector('div.show-list');
        hide(header);
    }
}

function toggleLanguage() {
    const languageSwitch = document.getElementById('language-switch');
    if (languageSwitch.checked) {
        console.log('Switch to English');
    } else {
        console.log('Switch to French');
    }
}


function toggleTheme() {
    const themeSwitch = document.getElementById('theme-switch');
    if (themeSwitch.checked) {
        document.body.classList.remove('light-mode');
        document.body.classList.add('dark-mode');
        console.log('Dark theme enabled');
    } else {
        document.body.classList.remove('dark-mode');
        document.body.classList.add('light-mode');
        console.log('Light theme enabled');
    }
}



// Code menus principaux
function widget(x) {
    // Récupère la ligne ayant la classe "Visible" pour la supprimer et la remplacer par la classe "Contenu"
    var see = document.querySelector(".Visible");
    see.classList.remove("Visible");
    see.classList.add("Contenu");
    // Liste toutes les lignes ayant la classe "Contenu"
    let contents = document.querySelectorAll(".Contenu");
    // Supprime de la ligne ayant la meme position que le nombre en paramètre la classe "Contenu" pour la remplacer par "Visible"
    contents[x].classList.remove("Contenu");
    contents[x].classList.add("Visible");

    var now = document.querySelector(".Current")
    now.classList.remove("Current");
    let span =  document.querySelectorAll("section span");
    span[x].classList.add("Current")
}

// Fonction pour envoyer un message
function sendMessage(event) {
    event.preventDefault();

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    const fileInput = document.getElementById('file-input');
    const receiverId = document.querySelector('input[name="receiver_id"]').value;

    if (!message && !fileInput.files.length) {
        alert("Veuillez entrer un message ou sélectionner un fichier.");
        return;
    }

    const formData = new FormData();
    formData.append("receiver_id", receiverId);
    formData.append("message", message);
    if (fileInput.files.length > 0) {
        formData.append("file", fileInput.files[0]);
    }

    fetch("SendMessage.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
                //console.log("Réponse du serveur : ", data);
            } catch (error) {
                //console.error("Erreur lors de l'analyse du JSON: ", error);
                //console.error("Réponse du serveur: ", text);
                alert("Erreur lors de l'envoi du message. Veuillez réessayer plus tard.");
                return;
            }
            if (data.status === 'success') {
                displayMessage(
                    data.message,
                    data.file_path,
                    'self',
                    data.timestamp,
                    data.message_id
                );
                messageInput.value = '';
                fileInput.value = '';
            } else {
                alert("Erreur: " + data.message);
            }
        })
        .catch(error => console.error("Erreur lors de l'envoi du message: ", error));
}

function displayMessage(messageContent, filePath = null, messageType, timestamp, messageId) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', messageType);
    messageElement.dataset.messageId = messageId;

    // Ajouter le texte du message s'il existe
    if (messageContent) {
        const messageText = document.createElement('p');
        messageText.innerHTML = messageContent;
        messageElement.appendChild(messageText);
    }

    // Ajouter un lien vers le fichier s'il existe
    if (filePath) {
        const fileLink = document.createElement('a');
        fileLink.href = filePath;
        fileLink.download = true;
        // Extraire le nom du fichier à partir du chemin
        const fileName = filePath.substring(filePath.lastIndexOf('/') + 1);
        fileLink.textContent = fileName || 'Télécharger le fichier';
        messageElement.appendChild(fileLink);
    }

    // Ajouter le timestamp
    const timestampContainer = document.createElement('div');
    timestampContainer.classList.add('timestamp-container');
    timestampContainer.innerHTML = `<span class="timestamp">${formatTimestamp(timestamp)}</span>`;

    messageElement.appendChild(timestampContainer);

    const chatBody = document.getElementById('chat-body');
    chatBody.appendChild(messageElement);
    chatBody.scrollTop = chatBody.scrollHeight; // Faire défiler vers le bas
}


function fetchMessages() {
    const receiverId = document.querySelector('input[name="receiver_id"]').value;
    const formData = new FormData();
    formData.append("receiver_id", receiverId);

    fetch("fetchMessages.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateChat(data.messages);
            } else {
                console.error("Erreur: " + data.message);
            }
        })
        .catch(error => console.error("Erreur lors de la récupération des messages: ", error));
}

// Fonction pour mettre à jour le chat dynamiquement
function updateChat() {
    if (!currentChatContactId) return; // Si aucun contact n'est sélectionné, ne rien faire

    fetch('../View/Principal/GetMessages.php?contact_id=' + currentChatContactId)
        .then(response => response.text())
        .then(html => {
            const chatBody = document.getElementById('chat-body');
            // Sauvegarder la position de défilement actuelle
            const previousScrollHeight = chatBody.scrollHeight;
            const isAtBottom = chatBody.scrollTop + chatBody.clientHeight >= previousScrollHeight - 10;

            // Mettre à jour le contenu du chat
            chatBody.innerHTML = html;

            // Si l'utilisateur était en bas du chat, faire défiler jusqu'en bas
            if (isAtBottom) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        })
        .catch(error => console.error('Erreur:', error));
}

// Actualiser le chat toutes les 5 secondes
//DON'T touch  setInterval(updateChat, 5000);

function sendFile(fileInput) {
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append("file", file);
    formData.append("receiver_id", document.querySelector('input[name="receiver_id"]').value);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "UploadFile.php", true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                displayMessage(`<a href="${response.file_path}" download="${file.name}">${file.name}</a>`, 'self', response.timestamp);
            } else {
                alert('Ошибка при загрузке файла: ' + response.message);
            }
        }
    };

    xhr.send(formData);

    // Clearing the selected file
    fileInput.value = '';
}

// Formatting Date and Time
function formatTimestamp(timestamp) {
    try {
        console.log('Timestamp reçu pour formatage :', timestamp);
        const messageDate = new Date(timestamp);

        if (isNaN(messageDate.getTime())) {
            console.error('Invalid date format:', timestamp);
            return 'Date invalide';
        }

        const now = new Date();

        const isToday = now.toDateString() === messageDate.toDateString();

        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        const isYesterday = yesterday.toDateString() === messageDate.toDateString();

        // Formater l'heure manuellement
        let hours = messageDate.getHours().toString().padStart(2, '0');
        let minutes = messageDate.getMinutes().toString().padStart(2, '0');
        let formattedTime = hours + ':' + minutes;

        // Formater la date manuellement
        let day = messageDate.getDate().toString().padStart(2, '0');
        let month = (messageDate.getMonth() + 1).toString().padStart(2, '0'); // Les mois commencent à 0
        let year = messageDate.getFullYear();
        let formattedDate = day + '.' + month + '.' + year;

        if (isToday) {
            return 'Aujourd\'hui ' + formattedTime;
        } else if (isYesterday) {
            return 'Hier ' + formattedTime;
        } else {
            return formattedDate + ' ' + formattedTime;
        }
    } catch (e) {
        console.error('Erreur lors du formatage du timestamp:', e);
        return 'Date invalide';
    }
}

function searchContacts() {
    const input = document.getElementById('search-input');
    const filter = input.value.toLowerCase();
    const contacts = document.querySelectorAll('#contacts-list li');

    contacts.forEach(contact => {
        const text = contact.textContent.toLowerCase();
        if (text.includes(filter)) {
            contact.style.display = '';
        } else {
            contact.style.display = 'none';
        }
    });
}

function openChat(contactId, contactName) {
    // Mettre à jour l'en-tête du chat avec le nom du contact
    document.getElementById('chat-header-title').innerText = 'Chat avec ' + contactName;

    // Enregistrer l'ID du contact dans le Local Storage
    localStorage.setItem('selectedContactId', contactId);

    // Enregistrer le nom du contact dans le Local Storage
    localStorage.setItem('selectedContactName', contactName);

    // Sauvegarder l'ID du contact actuel pour envoyer un message
    window.currentChatContactId = contactId;

    // Définir la valeur de receiver_id
    document.getElementById('receiver_id').value = contactId;

    // Cacher l'indicateur de nouveau message pour ce contact
    hideNewMessageIndicator(contactId);

    // Supprimer la classe 'contact-active' de tous les contacts
    const contacts = document.querySelectorAll('#contacts-list li');
    contacts.forEach(contact => {
        contact.classList.remove('contact-active');
    });

    // Ajouter la classe 'contact-active' au contact sélectionné
    const activeContact = document.querySelector(`#contacts-list li[data-contact-id="${contactId}"]`);
    if (activeContact) {
        activeContact.classList.add('contact-active');
    }

    // Récupérer les messages via une requête AJAX
    fetch('../View/Principal/GetMessages.php?contact_id=' + contactId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('chat-body').innerHTML = html;
            // Faire défiler le chat vers le bas
            document.getElementById('chat-body').scrollTop = document.getElementById('chat-body').scrollHeight;
        })
        .catch(error => console.error('Erreur:', error));
}

function showDefaultChatMessage() {
    const chatBody = document.getElementById('chat-body');
    chatBody.innerHTML = '<div class="default-message">Sélectionnez un chat pour commencer la conversation.</div>';
    document.getElementById('chat-header-title').innerText = 'Messagerie';
}
// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const selectedContactId = localStorage.getItem('selectedContactId');
    const selectedContactName = localStorage.getItem('selectedContactName');

    if (selectedContactId && selectedContactName) {
        // Ouvrir le chat avec le contact précédemment sélectionné
        showDefaultChatMessage(); //openChat(selectedContactId, selectedContactName);
    } else {
        // Aucun contact sélectionné, afficher le message par défaut
        showDefaultChatMessage();
    }
});

//------ Notifications realisation -------------------------------//

function updateNotifications(newMessages) {
    const notificationCountElement = document.getElementById('notification-count');

    if (newMessages.length > 0) {
        // Afficher le nombre total de nouveaux messages
        let totalNewMessages = newMessages.reduce((sum, msg) => sum + parseInt(msg.message_count), 0);
        notificationCountElement.textContent = totalNewMessages;
        notificationCountElement.style.display = 'block';

        // Mettre à jour les indicateurs sur les contacts
        newMessages.forEach(msg => {
            const contactId = msg.sender_id;
            showNewMessageIndicator(contactId);
        });
    } else {
        // Pas de nouveaux messages
        notificationCountElement.style.display = 'none';

        // Cacher les indicateurs sur les contacts
        const indicators = document.querySelectorAll('.new-message-indicator');
        indicators.forEach(indicator => {
            indicator.style.display = 'none';
        });
    }
}



// Vérifier les nouveaux messages toutes les 1 secondes
setInterval(checkForNewMessages, 1000);

function checkForNewMessages() {
    fetch('CheckNewMessages.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                updateNotifications(data.new_messages);
            } else {
                console.error('Erreur lors de la vérification des nouveaux messages:', data.message);
            }
        })
        .catch(error => console.error('Erreur réseau lors de la vérification des nouveaux messages:', error));
}

document.getElementById('notification-icon').addEventListener('click', function() {
    // Récupérer les nouveaux messages
    fetch('GetNewMessages.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                data.new_messages.forEach(msg => {
                    const contactId = msg.sender_id;
                    const contactElement = document.querySelector(`#contacts-list li[data-contact-id="${contactId}"]`);
                    if (contactElement) {
                        const contactName = contactElement.textContent.trim();
                        openChat(contactId, contactName);
                    }
                });

                // Réinitialiser les notifications
                resetNotifications();
            } else {
                console.error('Erreur lors de la récupération des nouveaux messages:', data.message);
            }
        })
        .catch(error => console.error('Erreur réseau lors de la récupération des nouveaux messages:', error));
});

//Reset notifications if user have seen it

function resetNotifications() {
    // Cacher la pastille sur la cloche
    const notificationCountElement = document.getElementById('notification-count');
    notificationCountElement.style.display = 'none';

    // Cacher les indicateurs sur les contacts
    const indicators = document.querySelectorAll('.new-message-indicator');
    indicators.forEach(indicator => {
        indicator.style.display = 'none';
    });

    // Mettre à jour le temps de la dernière vérification côté serveur
    fetch('ResetNotifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                console.error('Erreur lors de la réinitialisation des notifications:', data.message);
            }
        })
        .catch(error => console.error('Erreur réseau lors de la réinitialisation des notifications:', error));
}

function showNewMessageIndicator(contactId) {
    const contactElement = document.querySelector(`#contacts-list li[data-contact-id="${contactId}"]`);
    if (contactElement) {
        const indicator = contactElement.querySelector('.new-message-indicator');
        if (indicator) {
            indicator.style.display = 'inline-block';
        }
    }
}

function hideNewMessageIndicator(contactId) {
    const contactElement = document.querySelector(`#contacts-list li[data-contact-id="${contactId}"]`);
    if (contactElement) {
        const indicator = contactElement.querySelector('.new-message-indicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }
}


// -------------------------------------------------------------------------------------//


// ---------------------------------- Student list -------------------------------------//
document.getElementById('sidebar-toggle').addEventListener('click', function() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('visible'); // Toggle la visibilité de la sidebar
    this.classList.toggle('active'); // Toggle la classe active pour la flèche
});

function searchStudents() {
    const input = document.getElementById('search-input').value.toLowerCase();
    const students = document.querySelectorAll('.student');

    students.forEach(student => {
        const name = student.textContent.toLowerCase();
        if (name.includes(input)) {
            student.style.display = '';
        } else {
            student.style.display = 'none';
        }
    });
}

function selectStudent(element) {

    const students = document.querySelectorAll('.student');
    students.forEach(student => {
        student.classList.remove('selected');
    });

    element.classList.add('selected');

    const studentId = element.getAttribute('data-student-id');
    const studentName = element.textContent.trim();

    // Openchat with the selected student
    openChat(studentId, studentName);
}



document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebar-toggle');

    if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
        sidebar.classList.remove('visible');
        toggleButton.classList.remove('active');
    }
});

//

// -----------------------------------------------------------------------//
// send a message only by clicking the button
document.getElementById('message-input').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
});


// ---------------------------------- Close the session -------------------------------------//

window.onbeforeunload = function() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "Logout.php", false);  // Use a synchronous request to end the session
    xhr.send(null);
};
