
// Ajouter une classe d'animation
document.querySelectorAll('.form-control, .form-control-file').forEach(element => {
    element.addEventListener('focus', () => {
        element.classList.add('animated-border');
    });

    element.addEventListener('blur', () => {
        element.classList.remove('animated-border');
    });
});

// Animation de validation du fichier lors de la sélection
document.getElementById('file').addEventListener('change', function() {
    if (this.files.length > 0) {
        // Afficher le bouton d'annulation
        document.getElementById('resetFileBtn').style.display = 'block';
    } else {
        document.getElementById('resetFileBtn').style.display = 'none';
    }
});

// Fonction pour réinitialiser le champ de fichier lorsque le bouton d'annulation est cliqué
document.getElementById('resetFileBtn').addEventListener('click', function() {
    const fileInput = document.getElementById('file');
    fileInput.value = ''; // Réinitialise le champ de fichier
    this.style.display = 'none'; // Cache le bouton d'annulation
});

// Animation lors de la saisie du texte
const messageInput = document.getElementById('message');
messageInput.addEventListener('input', () => {
    messageInput.classList.add('typing-animation');
    clearTimeout(messageInput.typingTimer);
    messageInput.typingTimer = setTimeout(() => {
        messageInput.classList.remove('typing-animation');
    }, 500);
});