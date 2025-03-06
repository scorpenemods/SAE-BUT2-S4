function toggleLanguage() {
    const languageSwitch = document.getElementById('language-switch');
    if (languageSwitch.checked) {
        console.log('Switch to English');
    } else {
        console.log('Switch to French');
    }
}

function toggleTheme() {
    let switches = document.querySelectorAll("input[type='checkbox']#theme-switch");

    const themeSwitch1 = switches[0];
    const themeSwitch2 = switches[1];
    const footerSwitch = document.getElementById('footer');
    if (themeSwitch1.checked || themeSwitch2.checked) {
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

