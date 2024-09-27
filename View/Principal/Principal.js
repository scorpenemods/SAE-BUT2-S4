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
function turn() {
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

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value;
    if (message.trim() !== "") {
        const timestamp = new Date().toLocaleTimeString('fr-FR', { timeZone: 'Europe/Paris' });
        displayMessage(`${message} <span class="timestamp">${timestamp}</span>`, 'self');
        messageInput.value = ''; // Очистить поле после отправки
    }
}

function sendFile(event) {
    const file = event.target.files[0];
    if (file) {
        const fileURL = URL.createObjectURL(file); // Генерация ссылки для скачивания
        displayMessage(`<a href="${fileURL}" download="${file.name}">${file.name}</a>`, 'self', true);
    }
}

function displayMessage(content, sender, isFile = false) {
    const chatBody = document.getElementById('chat-body');
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', sender);
    messageElement.innerHTML = content;
    chatBody.appendChild(messageElement);
    chatBody.scrollTop = chatBody.scrollHeight; // Автопрокрутка вниз
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

    function toggleMenu() {
        const menu = document.getElementById('settingsMenu');
        menu.classList.toggle('hide-list');
        menu.classList.toggle('show-list');
    }
    function widget(index) {
        // Получаем все элементы содержимого и кнопки меню
        const contents = document.querySelectorAll('.Contenus .Contenu');
        const buttons = document.querySelectorAll('.widget-button');

        // Убираем активный класс "Visible" с контента и "Current" с кнопок
        contents.forEach((content) => content.classList.remove('Visible'));
        buttons.forEach((button) => button.classList.remove('Current'));

        // Добавляем активный класс к выбранному контенту и кнопке
        contents[index].classList.add('Visible');
        buttons[index].classList.add('Current');
    }
}