<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
require_once '../Model/Note.php';

global $database;
$database = Database::getInstance();

$statusMessage = null;
$statusColor = null;

// Vérifiez si la méthode est POST pour traiter les données soumises
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'] ?? null; // Remarque : vérifiez le nom correct du champ
    $notesData = $_POST['notes'] ?? [];

    if (!$studentId || !is_array($notesData)) {
        $statusMessage = "Erreur : Identifiant étudiant ou données des notes invalides.";
        $statusColor = "red";
    } else {
        try {
            $pdo = $database->getConnection();
            $database->addNotes($studentId, $notesData, $pdo);
            // Après le traitement, redirigez avec PRG
            header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            // Redirection en cas d'erreur
            header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=error");
            exit;
        }
    }
}
// Récupération des données pour l'affichage
$studentId = $_GET['student_id'] ?? null;
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

// Récupérez le message de statut s'il est défini
$status = $_GET['status'] ?? null;
if ($status === 'success') {
    $statusMessage = "Les notes ont été enregistrées avec succès.";
    $statusColor = "green";
} elseif ($status === 'error') {
    $statusMessage = "Erreur lors de l'enregistrement des notes. Veuillez réessayer.";
    $statusColor = "red";
}
?>



<form id="noteForm" action="Professor.php" method="post">
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

    <button type="button" id="addNote" class="mainbtn" onclick="addNoteRow()">Ajouter une note</button>
    <button type="submit">Enregistrer toutes les notes</button>

</form>
<div id="validationMessage" class="validation-message"></div>
