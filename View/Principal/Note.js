/*
 * Ce script gère l'affichage des sous-tableaux, la mise à jour des valeurs des curseurs
 * et la gestion des formulaires de notation avec une confirmation modale.
 * Il permet d'afficher/masquer des détails, de mettre à jour dynamiquement les valeurs
 * des curseurs et d'assurer que les utilisateurs confirment leurs actions avant de soumettre les formulaires.
 */
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


function saveNoteProf(noteId, description, value, studentId) {
    fetch("GetNotesProf.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            student_id: studentId,
            note_id: noteId,
            description: description,
            value: value
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log("Succès :", data);
        })
        .catch(error => {
            console.error("Erreur :", error);
        });
}

document.addEventListener("DOMContentLoaded", function () {
    const sliders = document.querySelectorAll("input[type=range]");

    sliders.forEach(slider => {
        slider.addEventListener("input", function () {
            const noteId = this.name.match(/\d+/)[0]; // Extraire l'ID de la note
            const description = this.name.match(/\[([a-z]+)\]/i)[1]; // Extraire la compétence évaluée
            const value = this.value;
            const studentId = document.getElementById("student-id").value;

            saveNoteProf(noteId, description, value, studentId);
        });
    });
});













