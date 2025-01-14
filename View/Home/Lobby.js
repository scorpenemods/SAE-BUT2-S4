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
    const footerSwitch = document.getElementById('footer');
    if (themeSwitch.checked) {
        const footerSwitch = document.getElementById('footer');
        document.body.classList.remove('light-mode');
        document.body.classList.add('dark-mode');
        footerSwitch.classList.add('dark-mode');
        console.log('Dark theme enabled');
    } else {
        document.body.classList.remove('dark-mode');
        document.body.classList.add('light-mode');
        footerSwitch.classList.remove('dark-mode');
        console.log('Light theme enabled');
    }
}