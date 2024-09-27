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

// Function to toggle the menu content
function toggleMenu(id, url) {
    const mainContent = document.getElementById('main-content');
    // Make a request to fetch the content from the specified URL
    fetch(url)
        .then(response => response.text())
        .then(data => {
            // Update the main content with the fetched data
            mainContent.innerHTML = data;
        });
}