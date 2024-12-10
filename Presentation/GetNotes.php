<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
require_once '../Model/Note.php';

global $database;
$database = Database::getInstance();


// Récupération de l'utilisateur connecté

$person = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;




// Récupération de l'étudiant sélectionné via `student_id`
$studentId = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;
$studentName = "Sélectionnez un étudiant";
$notes = [];

if ($studentId) {
    $student = $database->getUserById($studentId);
    if ($student) {
        $studentName = htmlspecialchars($student['prenom']) . ' ' . htmlspecialchars($student['nom']);
        $notes = $database->getNotes($studentId);
    } else {
        $studentName = "Étudiant introuvable";
    }
}
?>

<form method="GET" action="">
    <input type="hidden" id="student-id" name="student_id" value="<?= htmlspecialchars($studentId); ?>">
    <h2 id="selected-student-name"><?= $studentName; ?></h2>
    <div class="notes-container">
        <table id="notesTable" class="notes-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Sujet</th>
                <th>Appréciations</th>
                <th>Note /20</th>
                <th>Coefficient</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($notes)): ?>
                <?php foreach ($notes as $note): ?>
                    <tr id="row_<?= htmlspecialchars($note->getId()); ?>">
                        <td><?= htmlspecialchars($note->getId()); ?></td>
                        <td>
                            <textarea name="sujet_<?= htmlspecialchars($note->getId()); ?>" rows="1" disabled><?= htmlspecialchars($note->getSujet()); ?></textarea>
                        </td>
                        <td>
                            <textarea name="appreciations_<?= htmlspecialchars($note->getId()); ?>" rows="1" disabled><?= htmlspecialchars($note->getAppreciation()); ?></textarea>
                        </td>
                        <td>
                            <input type="number" name="note_<?= htmlspecialchars($note->getId()); ?>" value="<?= htmlspecialchars($note->getNote()); ?>" disabled>
                        </td>
                        <td>
                            <input type="number" name="coeff_<?= htmlspecialchars($note->getId()); ?>" value="<?= htmlspecialchars($note->getCoeff()); ?>" disabled>
                        </td>
                        <td>
                            <input type="hidden" name="note_id[]" value="<?= htmlspecialchars($note->getId()); ?>">
                            <button type="button" id="edit_<?= htmlspecialchars($note->getId()); ?>" name="saveNotes" class="mainbtn" onclick="editOrSave(<?= htmlspecialchars($note->getId()); ?>)">Modifier les notes</button>
                            <button type="button" name="delete_note" class="btn btn-danger" onclick="showConfirmation(<?= htmlspecialchars($note->getId()); ?>, event)">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Aucune note disponible pour cet étudiant.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</form>
