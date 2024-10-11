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

    fetch("sendMessage.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error("Erreur lors de l'analyse du JSON: ", error);
                console.error("Réponse du serveur: ", text);
                alert("Erreur lors de l'envoi du message. Veuillez réessayer plus tard.");
                return;
            }
            if (data.status === 'success') {
                displayMessage(
                    data.message,
                    data.file_path,
                    data.file_name,
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

function displayMessage(messageContent, filePath = null, fileName = '', messageType, timestamp, messageId) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', messageType);
    messageElement.dataset.messageId = messageId; // Assign the message ID

    // Add message text if any
    if (messageContent) {
        const messageText = document.createElement('p');
        messageText.innerHTML = messageContent;
        messageElement.appendChild(messageText);
    }

    // Add file link if file exists
    if (filePath) {
        const fileLink = document.createElement('a');
        fileLink.href = filePath;
        fileLink.download = true;
        fileLink.textContent = fileName || 'Télécharger le fichier';
        messageElement.appendChild(fileLink);
    }

    // Add timestamp
    const timestampContainer = document.createElement('div');
    timestampContainer.classList.add('timestamp-container');
    timestampContainer.innerHTML = `<span class="timestamp">${formatTimestamp(timestamp)}</span>`;

    messageElement.appendChild(timestampContainer);

    const chatBody = document.getElementById('chat-body');
    chatBody.appendChild(messageElement);
    chatBody.scrollTop = chatBody.scrollHeight; // Scroll to the bottom
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

function updateChat(messages) {
    const chatBody = document.getElementById('chat-body');
    chatBody.innerHTML = ''; // Clear existing messages

    messages.forEach(msg => {
        const messageType = msg.sender_id == currentUserId ? 'self' : 'other';
        displayMessage(msg.content, msg.file_path, messageType, msg.timestamp);
    });
}

// setInterval(fetchMessages, 5000); // Fetch messages every 5 seconds

function sendFile(fileInput) {
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append("file", file);
    formData.append("receiver_id", document.querySelector('input[name="receiver_id"]').value);

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "uploadFile.php", true);

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
    const optionsTime = { hour: '2-digit', minute: '2-digit', timeZone: 'Europe/Paris' };
    const optionsDate = { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'Europe/Paris' };

    // Get the current date and time in the Europe/Paris time zone
    const nowParis = new Date().toLocaleString('en-US', { timeZone: 'Europe/Paris' });
    const now = new Date(nowParis);

    // Get the date and time of the message in the Europe/Paris time zone
    const messageDateParis = new Date(timestamp).toLocaleString('en-US', { timeZone: 'Europe/Paris' });
    const messageDate = new Date(messageDateParis);

    // Checking if the message is today's
    const isToday = now.toDateString() === messageDate.toDateString();

    // Checking if the message is from yesterday
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    const isYesterday = yesterday.toDateString() === messageDate.toDateString();

    if (isToday) {
        return 'Today ' + messageDate.toLocaleTimeString('fr-FR', optionsTime);
    }
    if (isYesterday) {
        return 'Yesterday ' + messageDate.toLocaleTimeString('fr-FR', optionsTime);
    }
    return messageDate.toLocaleDateString('fr-FR', optionsDate) + ' ' + messageDate.toLocaleTimeString('fr-FR', optionsTime);
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
