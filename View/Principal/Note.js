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

// valider l'ajout des notes
function validateNotes() {
    const inputs = document.querySelectorAll('.notes-table input[name="note[]"], .notes-table input[name="coeff[]"]');
    let valid = true;

    // Validation des champs de notes et coefficients
    inputs.forEach(input => {
        const value = input.value.trim();
        if (value !== '') {
            const numericValue = parseFloat(value);
            if (isNaN(numericValue) || numericValue < 0 || numericValue > 20) {
                valid = false;
                input.style.borderColor = 'red';
            } else {
                input.style.borderColor = '';
            }
        } else {
            valid = false; // Si un champ est vide, marquer la validation comme fausse
            input.style.borderColor = 'red';
        }
    });

    const validationMessage = document.getElementById('validationMessage');

    if (valid) {
        validationMessage.textContent = 'Notes validées avec succès !';
        validationMessage.style.color = 'green';

        // Préparer et envoyer les données au backend
        const form = document.getElementById('notesForm');
        const formData = new FormData(form);

        // Ajoutez explicitement l'ID de l'étudiant au FormData si nécessaire
        const studentId = document.getElementById('student-id').value;
        formData.append('student_id', studentId);

        fetch('GetNotes.php', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur de la requête réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Les notes ont été ajoutées avec succès.');

                    // Désactiver les champs nouvellement ajoutés
                    const newNoteRows = document.querySelectorAll('.new-note-row');
                    newNoteRows.forEach(row => {
                        row.classList.remove('new-note-row');
                        row.querySelectorAll('textarea, input').forEach(element => {
                            element.setAttribute('disabled', 'true');
                            element.style.backgroundColor = '#d3d3d3';
                        });
                    });

                    // Désactiver les boutons Valider et Annuler après l'ajout
                    document.getElementById('validateBtn').setAttribute('disabled', 'true');
                    document.getElementById('cancelBtn').setAttribute('disabled', 'true');
                    document.getElementById('addNoteButton').removeAttribute('disabled');
                } else {
                    alert('Erreur lors de l\'ajout des notes : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout des notes :', error);
                alert('Une erreur est survenue lors de l\'ajout des notes.');
            });
    } else {
        validationMessage.textContent = 'Veuillez remplir tous les champs avec des notes valides entre 0 et 20.';
        validationMessage.style.color = 'red';
    }
}

//Ajouter une nouvelle ligne de notes
function addNoteRow() {
    const table = document.getElementById('notesTable').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;

    // Limiter le nombre de lignes de notes à un maximum de 4
    if (rowCount >= 4) {
        const validationMessage = document.getElementById('validationMessage');
        validationMessage.textContent = 'Vous ne pouvez pas ajouter plus de 4 notes.';
        setTimeout(() => {
            validationMessage.textContent = '';
        }, 3000);
        validationMessage.style.color = 'red';
        return;
    }

    const newRow = table.insertRow();
    const newId = `new-${rowCount + 1}`;
    newRow.classList.add('new-note-row');

    newRow.innerHTML = `
      
        <td><textarea name="sujet[]" rows="1"></textarea></td>
        <td><textarea name="appreciations[]" rows="1"></textarea></td>
        <td><input type="number" name="note[]" required></td>
        <td><input type="number" name="coeff[]" required></td>
        <td><button type="button" onclick="deleteNoteRow(this)">Supprimer</button></td>
    `;

    // Activer les boutons Valider et Annuler
    document.getElementById('validateBtn').removeAttribute('disabled');
    document.getElementById('cancelBtn').removeAttribute('disabled');
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

// pop up suppression
function showConfirmation(noteId, event) {
    if (event) {
        event.preventDefault();
    }

    let popup = document.getElementById('confirmationPopup');
    if (!popup) {
        popup = document.createElement('div');
        popup.id = 'confirmationPopup';
        popup.className = 'popup';

        popup.innerHTML = `
            <div class="popup-content">
                <p>Voulez-vous vraiment supprimer cette note ?</p>
                <div class="popup-buttons">
                    <button id="confirmDelete" class="btn btn-danger">Valider</button>
                    <button id="cancelDelete" class="btn btn-secondary">Annuler</button>
                </div>
            </div>
        `;

        document.body.appendChild(popup);
    }

    popup.style.display = 'flex';

    const confirmDelete = document.getElementById('confirmDelete');
    const cancelDelete = document.getElementById('cancelDelete');

    // Remove previous event listeners if present
    confirmDelete.onclick = null;
    cancelDelete.onclick = null;

    confirmDelete.onclick = function () {
        const form = new FormData();
        form.append('note_id', noteId);
        form.append('delete_note', '1');

        fetch('GetNotes.php', {
            method: 'POST',
            body: form
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur de la requête réseau');
                }
                return response.text();
            })
            .then(data => {
                // Remove the row from the table if deletion is successful
                if (data.includes("success")) {
                    const row = document.querySelector(`#row_${noteId}`);
                    if (row) {
                        row.remove();
                    }
                    popup.style.display = 'none';
                } else {
                    alert('Erreur lors de la suppression de la note : ' + data);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression de la note :', error);
                alert('Une erreur est survenue lors de la suppression de la note.');
            });
    };

    cancelDelete.onclick = function () {
        popup.style.display = 'none';
    };
}

// pop up
function editOrSave(noteId) {
    const row = document.getElementById(`row_${noteId}`);
    if (!row) {
        return;
    }

    const editButton = document.getElementById(`edit_${noteId}`);
    const inputs = row.querySelectorAll('input[type="number"]');
    const textareas = row.querySelectorAll('textarea');

    if (editButton.innerText === "Modifier les notes") {
        inputs.forEach(input => {
            input.disabled = false;
            input.style.backgroundColor = "#ffffff";
        });
        textareas.forEach(textarea => {
            textarea.disabled = false;
            textarea.style.backgroundColor = "#ffffff";
        });

        editButton.innerText = "Sauvegarder les notes";
        editButton.onclick = function() {
            saveModification(noteId);
        };
    }
}

function saveModification(noteId){
    fetch(`GetNotes.php?note_id=${noteId}`)

}

document.addEventListener('DOMContentLoaded', function() {
    // Code à exécuter après le chargement complet du DOM
    document.querySelectorAll('[id^="edit_"]').forEach(button => {
        button.addEventListener('click', function() {
            const noteId = this.id.split('_')[1];
            editOrSave(noteId);
        });
    });
});


// Fonction pour sélectionner un étudiant
function fetchNotesForStudent(studentId) {
    fetch(`GetNotes.php?student_id=${studentId}`)
        .then(response => response.text())
        .then(data => {
            // Mettre à jour le contenu du tableau des notes
            const notesTableBody = document.querySelector('#notesTable tbody');
            if (notesTableBody) {
                notesTableBody.innerHTML = data;
            } else {
                console.error("Impossible de trouver le corps de la table des notes.");
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération des notes :', error);
        });
    console.log("ID de l'étudiant pour les notes: ", studentId);
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
