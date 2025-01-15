// Fonction pour afficher/masquer le sous-tableau
function showUnderTable(button, idUnderTable) {
    const sousTableau = document.getElementById(idUnderTable);

    if (!sousTableau) {
        console.warn(`L'élément avec l'ID '${idUnderTable}' est introuvable.`);
        return; // Arrête la fonction si l'élément n'existe pas
    }

    // Alterner l'affichage entre visible et caché
    if (sousTableau.style.display === "none" || sousTableau.style.display === "") {
        sousTableau.style.display = "table-row";
        button.textContent = "Masquer Détails";
    } else {
        sousTableau.style.display = "none";
        button.textContent = "Afficher Détails";
    }
}

function updateValue(spanId, val) {
    console.log("Valeur du curseur :", val);

    const span = document.getElementById(spanId);
    if (span) {
        span.textContent = val;

    }
}

function submitSlider(slider) {
    const noteId = slider.getAttribute('data-note-id');
    const description = slider.getAttribute('data-description');
    const value = slider.value;

    document.getElementById('sliderNoteId').value = noteId;
    document.getElementById('sliderDescription').value = description;
    document.getElementById('sliderValue').value = value;

    document.getElementById('sliderForm').submit();
}

document.addEventListener('DOMContentLoaded', function () {
    const noteForm = document.getElementById('noteForm');
    const modal = document.getElementById('confirmationModal');
    const confirmButton = document.getElementById('confirmButton');
    const cancelButton = document.getElementById('cancelButton');

    noteForm.addEventListener('submit', function (event) {
        // Empêche la soumission par défaut
        event.preventDefault();
        // Affiche la boîte modale
        modal.style.display = 'block';
    });

    // Confirme l'action
    confirmButton.addEventListener('click', function () {
        modal.style.display = 'none';
        noteForm.submit(); // Soumet le formulaire
    });

    // Annule l'action
    cancelButton.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Ferme la boîte si l'utilisateur clique en dehors de celle-ci
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});












