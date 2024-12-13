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
function addNoteRow() {
    const table = document.getElementById('notesTable').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length;

    // Limiter à un maximum de 4 nouvelles lignes
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

    const idCell = newRow.insertCell(0);
    const sujetCell = newRow.insertCell(1);
    const appreciationCell = newRow.insertCell(2);
    const noteCell = newRow.insertCell(3);
    const coeffCell = newRow.insertCell(4);
    const actionCell = newRow.insertCell(5);

    idCell.textContent = `new-${newId}`;
    sujetCell.innerHTML = `<textarea name="notes[${newId}][sujet]" rows="1" required></textarea>`;
    appreciationCell.innerHTML = `<textarea name="notes[${newId}][appreciation]" rows="1"></textarea>`;
    noteCell.innerHTML = `<input type="number" name="notes[${newId}][note]" min="0" max="20" required>`;
    coeffCell.innerHTML = `<input type="number" name="notes[${newId}][coeff]" min="0" required>`;
    actionCell.innerHTML = `<button type="button" onclick="deleteNoteRow(this)">Supprimer</button>`;
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


