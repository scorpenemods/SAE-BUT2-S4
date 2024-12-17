<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
require_once '../Model/Note.php';

global $database;
$database = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null; // Identifier l'action
    $studentId = $_POST['student_id'] ?? null;

    if (!$studentId) {
        header("Location: Professor.php?status=error");
        exit;
    }

    try {
        $pdo = $database->getConnection();

        // Ajouter des notes
        if ($action === 'add_notes') {
            $notesData = $_POST['notes'] ?? [];

            if (!empty($notesData)) {
                $database->addNotes($studentId, $notesData, $pdo);
            }
        }

        // Mettre à jour les notes
        if ($action === 'update_notes') {
            $noteIds = $_POST['note_id'] ?? [];
            foreach ($noteIds as $noteId) {
                $sujet = $_POST["sujet_$noteId"] ?? null;
                $appreciation = $_POST["appreciations_$noteId"] ?? null;
                $note = $_POST["note_$noteId"] ?? null;
                $coeff = $_POST["coeff_$noteId"] ?? null;

                if ($sujet !== null && $note !== null && $coeff !== null) {
                    $database->updateNote($noteId, $studentId, $sujet, $appreciation, $note, $coeff, $pdo);
                }
            }
        }
        if ($action === 'delete_note') {
            $noteId = $_POST['note_id'] ?? null;

            if ($noteId !== null) {
                // Appel à la fonction deleteNote
                $database->deleteNote($noteId, $studentId, $pdo);

                // Redirection après la suppression
                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
                exit;
            } else {
                // Si noteId est null, erreur
                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=error");
                exit;
            }
        }

        // Redirection après succès
        header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=error");
        exit;
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
                            <button type="button" id="edit_<?= htmlspecialchars($note->getId()); ?>" class="mainbtn" name="action" value="update_notes" onclick="editNote(this)">Modifier les notes</button
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete_note">
                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId); ?>">
                                <input type="hidden" name="note_id" value="<?= htmlspecialchars($note->getId()); ?>">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
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
    <button type="submit" name="action" value="add_notes">Enregistrer toutes les notes</button>
    <button type="submit" name="action" value="update_notes">Sauvegarder les modifications</button>

</form>
<div id="validationMessage" class="validation-message"></div>
