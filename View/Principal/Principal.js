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

// Fonction pour mettre à jour le chat dynamiquement
function updateChat() {
    if (!currentChatContactId) return; // If no contact is selected, do nothing

    const chatBody = document.getElementById('chat-body');
    const previousScrollHeight = chatBody.scrollHeight;
    const scrollPosition = chatBody.scrollBottom;

    fetch('../View/Principal/GetMessages.php?contact_id=' + currentChatContactId)
        .then(response => response.text())
        .then(html => {
            chatBody.innerHTML = html;

            // Restore scroll position
            chatBody.scrollBottom = scrollPosition;

            // Optionally, scroll to the bottom if you prefer
            // chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(error => console.error('Erreur:', error));
}

// Actualiser le chat toutes les 5 secondes
setInterval(updateChat, 5000);

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

// Global variables
window.currentChatContactId = null;
window.currentGroupId = null;

// Attach event listener to messageForm
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('messageForm');

    messageForm.addEventListener('submit', function(event) {
        event.preventDefault();
        if (window.currentGroupId) {
            sendGroupMessage(event);
        } else if (window.currentChatContactId) {
            sendMessage(event);
        } else {
            alert('Veuillez sélectionner un chat pour envoyer un message.');
        }
    });
});

// Assign sendMessage to window for global access
window.sendMessage = sendMessage;
// Ensure updateChat is accessible globally
window.updateChat = updateChat;

// Function to open a private chat
function openChat(contactId, contactName) {
    // Update chat header
    document.getElementById('chat-header-title').innerText = 'Chat avec ' + contactName;

    // Set current chat context
    window.currentChatContactId = contactId;
    window.currentGroupId = null; // Reset group ID

    // Set hidden input values
    document.getElementById('receiver_id').value = contactId;
    document.getElementById('group_id').value = '';

    // Update the form action
    const messageForm = document.getElementById('messageForm');
    messageForm.action = 'SendMessage.php';

    // Hide new message indicator
    hideNewMessageIndicator(contactId);

    // Update active contact styling
    const contacts = document.querySelectorAll('#contacts-list li');
    contacts.forEach(contact => {
        contact.classList.remove('contact-active');
    });

    const activeContact = document.querySelector(`#contacts-list li[data-contact-id="${contactId}"]`);
    if (activeContact) {
        activeContact.classList.add('contact-active');
    }

    // Fetch messages via AJAX
    fetch('../View/Principal/GetMessages.php?contact_id=' + contactId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('chat-body').innerHTML = html;
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


// ---------------------------------- Livret de suivi -----------------------------------//

function showContent(x) {
    // Hide all content sections
    document.querySelectorAll('.content-section').forEach((section) => {
        section.classList.remove('active');
    });

    // Show the selected content section
    document.getElementById(`${x}`).classList.add('active');
}



// ---------------------------------- Add secretariat ----------------------------------//

function showForm(){
    document.getElementById("showButton").style.display = "none";
    document.getElementById("secretariatCreation").style.display = "block";
}

function hideForm() {
    document.getElementById("showButton").style.display = "inline";
    document.getElementById("secretariatCreation").style.display = "none";
    document.getElementById("formData").reset();
    document.getElementById("response").innerHTML = "";
}

// ---------------------------------- Student list -------------------------------------//
function sidebar(){
    const sidebar = document.getElementById('sidebar');
    const menu = document.getElementById('Menus');
    const arrow = document.getElementById('sidebar-toggle');
    menu.classList.toggle('responsive');
    sidebar.classList.toggle('visible'); // Toggle la visibilité de la sidebar
    arrow.classList.toggle('active'); // Toggle la classe active pour la flèche
}

function searchStudents() {
    const input = document.getElementById('search-input-sidebar').value.toLowerCase();
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
    openChat(studentId, studentName);

    const studentNameElement = document.getElementById('student-name');
    studentNameElement.textContent = studentName;
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

// -----------------------------------Notes--------------------------------------------------//
function enableNotes() {
    const inputs = document.querySelectorAll('.notes-table input');
    const textareas = document.querySelectorAll('.notes-table textarea');
    inputs.forEach(input => input.removeAttribute('disabled'));
    textareas.forEach(textarea => textarea.removeAttribute('disabled'));

    document.getElementById('validateBtn').removeAttribute('disabled');
    document.getElementById('cancelBtn').removeAttribute('disabled');
}

document.getElementById('editNotesButton').addEventListener('click', enableNotes);
document.getElementById('validateBtn').addEventListener('click', function() {
    validateNotes();
    document.querySelector('form').submit();
});



function autoExpand(element) {
    element.style.height = 'inherit';
    element.style.height = `${element.scrollHeight}px`;
}

function cancelNotes() {
    const inputs = document.querySelectorAll('.notes-table input');
    const textareas = document.querySelectorAll('.notes-table textarea');

    inputs.forEach(input => {
        input.setAttribute('disabled', '');
        input.value = ''; // Reset value
        input.style.borderColor = ''; // Reset border
    });

    textareas.forEach(textarea => {
        textarea.setAttribute('disabled', '');
        textarea.value = ''; // Reset value
        textarea.style.borderColor = ''; // Reset border
    });

    document.getElementById('validateBtn').setAttribute('disabled', '');
    document.getElementById('cancelBtn').setAttribute('disabled', '');

    document.getElementById('validationMessage').textContent = '';
}

document.getElementById('editNotesButton').addEventListener('click', enableNotes);
document.getElementById('cancelBtn').addEventListener('click', cancelNotes);

function validateNotes() {
    const inputs = document.querySelectorAll('.notes-table input');
    const textareas = document.querySelectorAll('.notes-table textarea');
    let valid = true;

    inputs.forEach(input => {
        const value = input.value.trim();
        if (value !== '') {
            const numericValue = parseFloat(value);
            if (isNaN(numericValue) || numericValue < 0 || numericValue > 20) {
                valid = false;
                input.style.borderColor = 'red';
            } else {
                input.style.borderColor = '';
            }
        } else {
            input.style.borderColor = '';
        }
    });

    textareas.forEach(textarea => {
        if (textarea.value.trim() !== '') {
            textarea.style.borderColor = '';
        } else {
            textarea.style.borderColor = '';
        }
    });

    const validationMessage = document.getElementById('validationMessage');
    if (valid) {
        validationMessage.textContent = 'Notes validées avec succès !';
        validationMessage.style.color = 'green';
    } else {
        validationMessage.textContent = 'Veuillez remplir tous les champs avec des notes valides entre 0 et 20.';
        validationMessage.style.color = 'red';
    }
}
document.getElementById('messageForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Professor.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('Notes added successfully!');
            } else {
                alert('Error: ' + response.message);
            }
        } else {
            alert('An error occurred while submitting the form.');
        }
    };
    xhr.send(formData);
});

function addNoteRow() {
    const table = document.getElementById('notesTable').getElementsByTagName('tbody')[0];
    const newRow = table.insertRow();


    const sujetCell = newRow.insertCell(0);
    const apreciationCell = newRow.insertCell(1);
    const noteCell = newRow.insertCell(2);
    const coeffCell = newRow.insertCell(3);


    sujetCell.innerHTML = '<textarea name="sujet[]" rows="">';
    apreciationCell.innerHTML = '<textarea name="appreciations[]" rows="0">';
    noteCell.innerHTML = '<input type="number" name="note[]" required>';
    coeffCell.innerHTML = '<input type="number" name="coeff[]" required>';
}


function saveNote() {
    const inputs = document.querySelectorAll('.notes-table input');
    const textareas = document.querySelectorAll('.notes-table textarea');
    let valid = true;

    inputs.forEach(input => {
        const value = input.value.trim();
        if (value !== '') {
            const numericValue = parseFloat(value);
            if (isNaN(numericValue) || numericValue < 0 || numericValue > 20) {
                valid = false;
                input.style.borderColor = 'red';
            } else {
                input.style.borderColor = '';
            }
        } else {
            input.style.borderColor = '';
        }
    });

    textareas.forEach(textarea => {
        if (textarea.value.trim() !== '') {
            textarea.style.borderColor = '';
        } else {
            textarea.style.borderColor = '';
        }
    });

    const validationMessage = document.getElementById('validationMessage');
    if (valid) {
        validationMessage.textContent = 'Notes validées avec succès !';
        validationMessage.style.color = 'green';
    } else {
        validationMessage.textContent = 'Veuillez remplir tous les champs avec des notes valides entre 0 et 20.';
        validationMessage.style.color = 'red';
    }

document.getElementById('messageForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'Professor.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert('Notes added successfully!');
            } else {
                alert('Error: ' + response.message);
            }
        } else {
            alert('An error occurred while submitting the form.');
        }
    };
    xhr.send(formData);
});
    }



