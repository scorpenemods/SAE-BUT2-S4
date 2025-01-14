// Permet aux zones de texte de s'agrandir selon la taille du texte inséré
function autoExpand(element) {
    element.style.height = 'inherit';
    element.style.height = `${element.scrollHeight}px`;
}
document.querySelectorAll('.notes-table textarea').forEach(textarea => {
    textarea.addEventListener('input', function() {
        autoExpand(this);
    });
});

// fonctionnement du bouton annuler
function cancelNotes() {
    const newNoteRows = document.querySelectorAll('.new-note-row');
    newNoteRows.forEach(row => row.remove());

    // Désactiver les boutons Valider et Annuler
    document.getElementById('validateBtn').setAttribute('disabled', 'true');
    document.getElementById('cancelBtn').setAttribute('disabled', 'true');

    const validationMessage = document.getElementById('validationMessage');
    validationMessage.textContent = '';
}

//Ajouter une nouvelle ligne de notes
function addUnderNoteRow(button) {
    const parentRow = button.closest('tr');
    const sousTableau = parentRow.querySelector('table tbody');
    if (!sousTableau) {
        console.warn("Aucun sous-tableau trouvé pour ajouter une ligne.");
        return;
    }

    // Déclarer et initialiser noteId AVANT de l'utiliser
    const noteId = button.getAttribute('data-note-id');
    const studentId = document.getElementById('student-id').value;

    console.log("noteId:", noteId, "studentId:", studentId);

    // Le reste du code
    const newRow = document.createElement('tr');
    newRow.classList.add('new-note-row');

    newRow.innerHTML = `
        <td colspan="5">
            <form method="POST" action="Professor.php">
                <input type="hidden" name="action" value="add_under_note">
                <input type="hidden" name="student_id" value="${studentId}">
                <input type="hidden" name="note_id" value="${noteId}">

                <table width="100%">
                    <tr>
                        <td>
                            <textarea name="description" rows="1" placeholder="Description" required></textarea>
                        </td>
                        <td>
                            <input type="number" name="under_note" min="0" max="20" placeholder="Note" required>
                        </td>
                        <td colspan="3">
                            <button type="submit">Enregistrer la note</button>
                            <button type="button" class="btn-delete" onclick="deleteNoteRow(this)">Supprimer</button>
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    `;

    sousTableau.appendChild(newRow);
}





function addNoteRow() {
    const table = document.getElementById('notesTable').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;

    // Créer une nouvelle ligne
    const newRow = table.insertRow();
    newRow.classList.add('new-note-row');

    const newId = `main-${rowCount + 1}`;
    const studentId = document.getElementById('student-id').value;

    // Ajouter un formulaire séparé pour chaque nouvelle ligne
    newRow.innerHTML = `
        <form method="POST" action="Professor.php" style="display: contents;">
            <input type="hidden" name="student_id" value="${studentId}">
            <td>${newId}</td>
            <td><textarea name="notes[${newId}][sujet]" rows="1" placeholder="Sujet" required></textarea></td>
            <td><input type="number" name="notes[${newId}][coeff]" min="0" placeholder="Coeff" required></td>
            <td>
                <button type="submit" name="action" value="add_notes">Enregistrer la note</button>
                <button type="button" onclick="deleteNoteRow(this)">Supprimer</button>
            </td>
        </form>
    `;
}


// supprimer une ligne de notes
function deleteNoteRow(button) {
    const row = button.parentElement.parentElement;
    row.remove();

    // Si aucune nouvelle note n'est présente, désactiver les boutons
    const newNoteRows = document.querySelectorAll('.new-note-row');
    if (newNoteRows.length === 0) {
        document.getElementById('validateBtn').setAttribute('disabled', 'true');
        document.getElementById('cancelBtn').setAttribute('disabled', 'true');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Vérifie si l'on est sur la section des notes au chargement
    const activeSection = "<?php echo $activeSection; ?>";
    if (activeSection === '6') {
        const firstStudentElement = document.querySelector('.student');
        if (firstStudentElement) {
            setTimeout(() => {
                selectStudent(firstStudentElement);
            }, 300);
        }
    }
});

// Fonction pour passer en mode édition sur une note
function editNote(button) {
    const row = button.closest('tr');
    const inputs = row.querySelectorAll('input, textarea');

    if (button.textContent.trim() === 'Modifier') {
        // Activer les champs pour les rendre modifiables
        inputs.forEach(input => {
            input.removeAttribute('disabled');
            input.style.backgroundColor = '#fff';
        });

        const formData = new FormData(document.getElementById('noteForm'));
        for (const [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Changer le texte du bouton pour enregistrer
        button.textContent = 'Enregistrer';
        button.onclick = function (event) {
            event.preventDefault();
            document.getElementById('formAction').value = 'update_notes';
            document.getElementById('noteForm').submit();
        };
    }
}






function saveChanges(button) {
    const form = document.getElementById('noteForm'); // Récupère le formulaire principal
    const row = button.closest('tr'); // Trouve la ligne en cours

    // Active tous les champs désactivés dans la ligne pour qu'ils soient soumis
    row.querySelectorAll('input, textarea').forEach(input => {
        input.removeAttribute('disabled');
    });

    // Définir l'action pour mettre à jour les notes
    document.getElementById('formAction').value = 'update_notes';

    form.submit(); // Soumet le formulaire principal
}



// Soumettre le formulaire
function submitForm() {
    const inputs = document.querySelectorAll('input:disabled, textarea:disabled');
    inputs.forEach(input => {
        input.removeAttribute('disabled'); // Activer temporairement les champs
    });

    document.getElementById('noteForm').submit(); // Soumettre le formulaire
}


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



function updateValue(displayId, value) {
    document.getElementById(displayId).textContent = value;
}









