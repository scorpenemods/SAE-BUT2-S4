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


