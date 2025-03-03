/*
 * Ce script gère les fonctionnalités liées aux groupes et à la messagerie :
 * - Ouvre et ferme les modales de création et d'édition de groupes.
 * - Soumet les formulaires pour créer ou mettre à jour des groupes via des requêtes fetch.
 * - Supprime les groupes et termine les stages après confirmation de l'utilisateur.
 * - Gère l'envoi de messages, y compris l'ajout de fichiers joints.
 * - Met à jour l'interface utilisateur en fonction des actions effectuées.
 */

// Vérifier au chargement si la classe doit être ajoutée
document.addEventListener("DOMContentLoaded", function() {
    if (sessionStorage.getItem("lastPage") === "Index.php") {
        sessionStorage.setItem("lastPage", "main");
        widget(0);
    }
    else{
        widget(localStorage.getItem('classAdded'));
        let savedStudent = localStorage.getItem('classAdded2');
        if (savedStudent) {
            let tempDiv = document.createElement("div"); // Créer un conteneur temporaire
            tempDiv.innerHTML = savedStudent; // Injecter le HTML
            let restoredElement = tempDiv.firstElementChild; // Extraire le vrai élément
            selectStudent(restoredElement)
        }
    }
});


// Code menu paramètre
// Afficher
function show(header) {
    header.classList.toggle("show-list");
    header.classList.toggle("hide-list");
}

// Cacher
function hide(header) {
    header.classList.toggle("hide-list");
    header.classList.toggle("show-list");
}

// Switcher entre les 2 en changeant la classe du body
function toggleMenu() {
    var header = document.querySelector('div.hide-list');
    try {
        (header.classList.contains("show-list"))
        show(header);
    } catch {
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
    let span = document.querySelectorAll("section span");
    span[x].classList.add("Current")

    localStorage.setItem('classAdded', x);
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
document.addEventListener('DOMContentLoaded', function () {
    const messageForm = document.getElementById('messageForm');

    messageForm.addEventListener('submit', function (event) {
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
document.addEventListener('DOMContentLoaded', function () {
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

document.getElementById('notification-icon').addEventListener('click', function () {
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


// ---------------------------------- Add Secretariat ----------------------------------//

function showForm() {
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
function sidebar() {
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

function fetchStudentInfoManage(userId) {
    fetch(`StudentManagment.php?user_id=${userId}`)
        .then(response => response.text())
        .then(data => {
            // Mettre à jour le contenu de la section d'information de l'étudiant avec les données reçues
            const studentDetails = document.querySelector('#student-infos');
            if (studentDetails) {
                studentDetails.innerHTML = data;
            } else {
                console.error("Impossible de trouver la section des détails de l'étudiant.");
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des informations de l\'étudiant :', error);
        });
    console.log("ID de l'étudiant pour le livret de suivi : ", userId);
}
function fetchStudentInfo(userId) {
    fetch(`LivretSuiviParticipant.php?user_id=${userId}`)
        .then(response => response.text())
        .then(data => {
            // Mettre à jour le contenu de la section d'information de l'étudiant avec les données reçues
            const studentDetails = document.querySelector('#student-details');
            if (studentDetails) {
                studentDetails.innerHTML = data;
            } else {
                console.error("Impossible de trouver la section des détails de l'étudiant.");
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des informations de l\'étudiant :', error);
        });
    console.log("ID de l'étudiant pour le livret de suivi : ", userId);
}

function selectStudent(element) {
    console.log("type : " + typeof element)
    localStorage.setItem('classAdded2', element.outerHTML);
    console.log("le storage : " + typeof localStorage.getItem('classAdded2'))
    console.log("Élément sélectionné : ", element);

    // Supprimer la classe 'selected' de tous les étudiants
    const students = document.querySelectorAll('.student');
    students.forEach(student => {
        student.classList.remove('selected');
    });

    // Ajouter la classe 'selected' à l'étudiant cliqué
    element.classList.add('selected');
    console.log("selectionné")

    // Récupérer l'ID de l'étudiant
    const studentId = element.getAttribute('data-student-id');
    if (!studentId) {
        console.error("L'ID de l'étudiant n'a pas pu être récupéré. Vérifiez la présence de l'attribut 'data-student-id'.");
        return;
    }

    // Mise à jour de l'ID dans l'input caché du formulaire
    document.getElementById('student-id').value = studentId;


    // Mettre à jour le nom de l'étudiant affiché
    const studentNameElement = document.getElementById('selected-student-name');
    if (studentNameElement) {
        studentNameElement.textContent = element.textContent.trim();
    } else {
        console.error("Impossible de trouver l'élément avec l'ID 'selected-student-name'");
    }
    if (isNotesTabActive()) {
        // Si l'onglet Notes est actif, soumettez le formulaire
        document.getElementById('noteForm').submit();
    } else {
        // Sinon, traitez la sélection sans recharger la page
        console.log(`Étudiant ${studentId} sélectionné hors de l'onglet Notes.`);
        // Ajoutez ici d'autres actions si nécessaire
    }
    // Charger les notes de l'étudiant sélectionné
    fetchStudentInfo(studentId);
    fetchStudentInfoManage(studentId);
    fetchNotes(studentId);
    fetchStage(studentId)
}

function fetchStage(stageId){
    fetch(`MissionStage.php?stage_id=${stageId}`)
        .then(response => response.text())
        .then(data => {
// Mettre à jour le contenu de la section d'information de l'étudiant avec les données reçues
            const mission = document.querySelector('#mission-details');
            if (mission) {
                mission.innerHTML = data;
            } else {
                console.error("Impossible de trouver la section des détails de l'étudiant.");
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des informations de l\'étudiant :', error);
        });
    console.log("ID de l'étudiant pour le livret de suivi : ", stageId);
}

function isNotesTabActive() {
    const notesTab = document.getElementById('content-6');
    return notesTab && notesTab.classList.contains('Visible');
}

document.getElementById('studentForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Empêche le rechargement de la page
    const studentId = document.getElementById('student-id').value;

    // Rechargez dynamiquement les données pour l'étudiant sélectionné
    window.location.href = `?student_id=${studentId}`;
});

function fetchNotes(studentId) {
    fetch(`GetNotes.php?student_id=${studentId}`)
        .then(response => response.text())
        .then(html => {
            // Mettre à jour le tableau des notes
            document.querySelector('.notes-container').innerHTML = html;

            // Récupérer le nom de l'étudiant dans le tableau chargé
            const nameElement = document.querySelector('.notes-container h2');
            if (nameElement) {
                document.getElementById('selected-student-name').textContent = nameElement.textContent.trim();
                nameElement.remove(); // Supprimer le h2 chargé pour éviter les doublons
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des notes :', error);
        });
}

// -----------------------------------------------------------------------//
// send a message only by clicking the button
document.getElementById('message-input').addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
        event.preventDefault();
    }
});


// ---------------------------------- Close the session -------------------------------------//

window.onbeforeunload = function () {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "Logout.php", false);  // Use a synchronous request to end the session
    xhr.send(null);
};






























