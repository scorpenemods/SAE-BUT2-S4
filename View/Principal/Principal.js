// Vérifier au chargement si la classe doit être ajoutée
document.addEventListener("DOMContentLoaded", function() {
    if (sessionStorage.getItem("lastPage") === "index.php") {
        sessionStorage.setItem("lastPage", "main");
        widget(0);
    }
    else{
        widget(localStorage.getItem('classAdded'));
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


// ---------------------------------- Livret de suivi -----------------------------------//

// Montre le contenu d'une rencontre
function showContent(x) {
    // Hide all content sections
    document.querySelectorAll('.content-section').forEach((section) => {
        section.classList.remove('active');
    });

    // Show the selected content section
    document.getElementById(`${x}`).classList.add('active');
}

let meetingCounter = 2;
let showcontent = 2;

// Ajoute une rencontre
function addMeeting() {
    const aside = document.querySelector(".livretbar");
    const depositSpan = aside.querySelector('span[onclick="showContent(1)"]');

    // Create a new meeting button
    const newMeeting = document.createElement("span");
    newMeeting.className = "vignette";
    newMeeting.textContent = `${meetingCounter}ème rencontre`;
    newMeeting.setAttribute("onclick", `showContent(${showcontent})`);

    // Insert the new meeting button before the deposit span
    aside.insertBefore(newMeeting, depositSpan);

    const lineBreak = document.createElement("br");
    aside.insertBefore(lineBreak, depositSpan);

    // Create the new content section for the meeting
    const contentContainer = document.querySelector(".content-livret");
    const newContent = document.createElement("div");
    newContent.className = "content-section";
    newContent.id = showcontent;
    const formContainerId = `formContainer-${showcontent}`;

    newContent.innerHTML = `
        <h3 style="padding: 10px">Formulaire</h3>
        <div class="livret-header">
            <h3>${meetingCounter}ème rencontre</h3>
        </div>

        <!-- Formulaire -->
            <div class="participants">
                <form method="post" id="${formContainerId}">
                    <p>
                    Date de rencontre : <label style="color: red">*</label> <br>

                    <input type="date" name="meeting"/>
                    </p>

                    <br><br>

                    <p>
                    Lieu de la rencontre : <label style="color: red">*</label> <br>

                    <input type="radio" id="Entreprise" name="Lieu"><label> En entreprise</label> <br>
                    <input type="radio" id="Tél" name="Lieu"><label> Par téléphone</label> <br>
                    <input type="radio" id="Visio" name="Lieu"><label> En visio</label> <br>
                    <input type="radio" id="Autre" name="Lieu"><label> Autre</label> <input type="text">
                    </p>

                    <br><br>

                    <button onclick="addField('${formContainerId}')" type="button">+ Ajouter un champ</button>

                </form>
            </div>
            <div style="display: flex; ">
                <!-- Validation du formulaire -->
                <div class="validation">
                    <h3 style="padding: 10px">Validation du formulaire</h3>

                    <button>Valider modifications</button>
                </div>

            </div>
    `;

    contentContainer.appendChild(newContent);

    meetingCounter++;
    showcontent++;

}


// créer un champ dans une rencontre
function addField(containerId) {
    const fieldWrapper = document.createElement('p');

    fieldWrapper.innerHTML = `
        <select name="field_choice" id="field_choice" onchange="removeDefaultOption(this)">
            <option value="" selected>Sélectionnez le type du champ</option>
            <option value="text">Text libre</option>
            <option value="qcm">QCM</option>
        </select>
        <button class="select-button" onclick="handleFieldSelection(this, '${containerId}')" type="button">Sélectionner</button> 
        <a class="cancel-link" onclick="deleteField(this)"> Annuler </a>
    `;

    const fieldContainer = document.getElementById(containerId);
    const addButton = fieldContainer.querySelector(`button[onclick="addField('${containerId}')"]`);
    fieldContainer.insertBefore(fieldWrapper, addButton);
}

//Permet de choisir un titre au nouveau champ
function handleFieldSelection(button, containerId) {
    const fieldWrapper = button.parentElement;
    const selectElement = button.previousElementSibling;
    const selectedType = selectElement.value;

    if (!selectedType) {
        alert("Veuillez sélectionner un type !");
        return;
    } else {
        const fieldText = document.createElement('form');
        fieldText.method = 'post';
        fieldText.innerHTML = `
            <label for="userText">Titre :</label>
            <input type="text" id="userText" name="userText" />
            <button type="submit">Valider</button>
            <a onclick="deleteField(this)"> Annuler </a>
        `;

        fieldText.addEventListener('submit', function(event) {
            event.preventDefault(); // Empêche le comportement par défaut (rechargement de la page)

            // Récupère la valeur de l'input
            const title = fieldText.querySelector('input[name="userText"]').value;

            // Ajoute le contenu en fonction du type choisi
            addFieldContent(containerId, selectedType, title);

            // Supprime le formulaire après validation
            fieldText.remove();
        });

        // Ajoute le formulaire dans le conteneur parent
        fieldWrapper.appendChild(fieldText);
    }

    // Supprime uniquement les éléments précédents créés par addField
    const elementsToRemove = fieldWrapper.querySelectorAll('select, .select-button, .cancel-link');
    elementsToRemove.forEach(element => element.remove());
}

// Créer le champ sélectionné par l'utilisateur (QCM ou text libre)
function addFieldContent(containerId, type, title) {
    const fieldWrapper = document.createElement('p');

    if (type === 'qcm') {
        fieldWrapper.innerHTML = `
            ${title} :
            <button class="edit-qcm" style="display: none;">Modifier</button>
            <button onclick="deleteField(this)" type="button">Supprimer</button> <br>
            <div class="radio-group">
            </div> <br class="last">
            <button type="button" class="add-option">+ Ajouter une réponse</button>
            <a class="save-qcm"> Enregistrer </a>
        `;

        // ajoute l'option de pouvoir ajouter une réponse
        fieldWrapper.querySelector('.add-option').addEventListener('click', function() {
            const radioGroup = fieldWrapper.querySelector('.radio-group');

            // Create a temporary form for user input
            const tempForm = document.createElement('div');
            tempForm.classList.add('temp-form');
            tempForm.innerHTML = `
                <input type="text" placeholder="Nom de l'option" class="new-option-input">
                <button type="button" class="confirm-option">Valider</button>
                <a class="cancel-option">Annuler</a>
            `;

            // Ajoute le forme
            const addOptionButton = fieldWrapper.querySelector('.add-option');
            fieldWrapper.insertBefore(tempForm, addOptionButton);

            // Validation du bouton d'ajout de réponse
            tempForm.querySelector('.confirm-option').addEventListener('click', function() {
                const inputValue = tempForm.querySelector('.new-option-input').value.trim();
                if (inputValue) {
                    const newOption = document.createElement('div');
                    newOption.innerHTML = `
                        <input type="radio" name=${title}>
                        <label>${inputValue}</label>
                        <a class="delete-option" style="color: red"> - Supprimer </a> <br> <br class="last">
                    `;


                    // Ajoute un event pour supprimer la réponse
                    newOption.querySelector('.delete-option').addEventListener('click', function() {
                        newOption.remove();
                    });

                    fieldWrapper.querySelector('br[class="last"]').remove();

                    radioGroup.appendChild(newOption);
                    tempForm.remove();
                } else {
                    alert('Veuillez entrer un nom pour l\'option.');
                }
            });

            // Ajoute l'option d'annuler la création d'une réponse
            tempForm.querySelector('.cancel-option').addEventListener('click', function() {
                tempForm.remove();
            });

            fieldWrapper.querySelector('.save-qcm').addEventListener('click', function () {
                // Désactive les boutons d'ajout et de modification
                fieldWrapper.querySelector('.add-option').style.display = 'none';
                fieldWrapper.querySelector('.save-qcm').style.display = 'none';

                // Supprime les liens "Supprimer" associés à chaque réponse
                fieldWrapper.querySelectorAll('.delete-option').forEach(option => option.style.display = 'none');

                // Bouton pour modifier
                const editButton = fieldWrapper.querySelector('.edit-qcm');
                editButton.style.display = 'inline-block';

                // Réactiver la possibilité de modifier le qcm
                editButton.addEventListener('click', function (event) {
                    event.preventDefault()
                    // Réactive les boutons de suppression et d'ajout
                    fieldWrapper.querySelector('.add-option').style.display = 'inline-block';
                    fieldWrapper.querySelector('.save-qcm').style.display = 'inline-block';
                    fieldWrapper.querySelectorAll('.delete-option').forEach(option => option.style.display = 'inline-block');

                    // Supprime le bouton "Modifier"
                    editButton.style.display = 'none';
                });
            });
        });
    } else if (type === 'text') {
        fieldWrapper.innerHTML = `
            ${title} :
            <button onclick="deleteField(this)" type="button">Supprimer</button> <br>
            <textarea name="remarque[]" class="textareaLivret"></textarea> <br><br>
        `;
    }

    const fieldContainer = document.getElementById(containerId);
    const addButton = fieldContainer.querySelector(`button[onclick="addField('${containerId}')"]`);
    fieldContainer.insertBefore(fieldWrapper, addButton);
}

//Supprime le formulaire
function deleteField(button) {
    button.parentElement.remove();
}

function deleteMeeting() {

    if (meetingCounter <= 2) {
        alert("Vous ne pouvez pas supprimer cette rencontre");
        return;
    }

    meetingCounter--;
    showcontent--;

    // enlève la dernière rencontre
    const aside = document.querySelector(".livretbar");
    const lastMeetingButton = aside.querySelector(`.vignette[onclick="showContent(${showcontent})"]`);
    const lastLineBreak = lastMeetingButton.nextElementSibling;

    if (lastMeetingButton) {
        aside.removeChild(lastMeetingButton);
    }
    if (lastLineBreak && lastLineBreak.tagName === "BR") {
        aside.removeChild(lastLineBreak);
    }

    const contentContainer = document.querySelector(".content-livret");
    const lastContent = document.getElementById(showcontent);

    // Enlève le contenu de la dernière rencontre
    if (lastContent) {
        contentContainer.removeChild(lastContent);
    }
}

function removeDefaultOption(selectElement) {
    const defaultOption = selectElement.querySelector('option[value=""]');
    if (selectElement.value !== "") {
        defaultOption.style.display = "none";
    } else {
        defaultOption.style.display = "block";
    }
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
    fetch(`livretSuiviParticipant.php?user_id=${userId}`)
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



function selectStudent(element) {
    console.log("Élément sélectionné : ", element);

    // Supprimer la classe 'selected' de tous les étudiants
    const students = document.querySelectorAll('.student');
    students.forEach(student => {
        student.classList.remove('selected');
    });

    // Ajouter la classe 'selected' à l'étudiant cliqué
    element.classList.add('selected');

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






























