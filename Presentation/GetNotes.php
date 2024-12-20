<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
require_once '../Model/Note.php';
require_once '../Model/UnderNote.php';

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
            $studentId = $_POST['student_id'] ?? null;

            if ($studentId && !empty($notesData)) {
                foreach ($notesData as $noteData) {
                    $sujet = $noteData['sujet'] ?? null;
                    $note = $noteData['note'] ?? null;
                    $coeff = $noteData['coeff'] ?? null;

                    $database->addNotes($studentId, $notesData, $pdo);
                }
                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
                exit;
            } else {
                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=error");
                exit;
            }
        }

        // Mettre à jour les notes
        if ($action === 'update_notes') {

            error_log("Updating notes for student ID: $studentId");
            $noteIds = $_POST['note_id'] ?? []; // Récupère les IDs des notes
            error_log("Updating notes for student ID: $studentId");
            error_log("Note IDs: " . print_r($noteIds, true)); // Déboguer les IDs des notes reçues

            foreach ($noteIds as $noteId) {
                $sujet = $_POST["sujet_$noteId"] ?? null; // Récupère le sujet
                $coeff = $_POST["coeff_$noteId"] ?? null; // Récupère le coefficient

                error_log("Processing Note ID: $noteId");
                error_log("Sujet: $sujet, Coefficient: $coeff"); // Vérifie les données reçues pour chaque note

                // Vérifiez si les champs sont définis
                if ($sujet !== null && $coeff !== null) {
                    try {
                        // Appel à la méthode de mise à jour de la note
                        $database->updateNote($noteId, $studentId, $sujet, $coeff, $pdo);
                        error_log("Note updated successfully for Note ID: $noteId");
                    } catch (Exception $e) {
                        error_log("Error updating Note ID: $noteId - " . $e->getMessage());
                    }
                } else {
                    error_log("Skipped updating Note ID: $noteId due to missing data");
                }
                error_log("Données reçues : " . print_r($_POST, true));

            }

            header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
            exit;
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

        if ($action === 'add_under_note') {
            $noteId = $_POST['note_id'] ?? null;
            $description = $_POST['description'] ?? null;
            $underNoteValue = $_POST['under_note'] ?? null;
            $studentId = $_POST['student_id'] ?? null;

            if ($noteId && $description && $underNoteValue !== null && $studentId) {
                // Construire un tableau de données conforme à addUnderNotes
                $notesData = [[
                    'description' => $description,
                    'note_id' => $noteId,
                    'note' => $underNoteValue
                ]];

                // Appel à la méthode addUnderNotes pour insérer la sous-note
                $database->addUnderNotes($notesData, $pdo);

                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
                exit;
            } else {
                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=error");
                exit;
            }
        }

        if ($action === 'delete_under_note') {
            $underNoteId = $_POST['under_note_id'] ?? null;

            if ($underNoteId !== null) {
                // Appel à la fonction deleteUnderNote
                $database->deleteUnderNote($underNoteId, $pdo);

                // Redirection après la suppression
                header("Location: Professor.php?student_id=" . urlencode($studentId) . "&status=success");
                exit;
            } else {
                // Si underNoteId est null, erreur
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
$notesWithAverage = [];

if ($studentId) {
    $student = $database->getUserById($studentId);
    if ($student) {
        $studentName = htmlspecialchars($student['prenom']) . ' ' . htmlspecialchars($student['nom']);
        $notes = $database->getNotes($studentId);

        // Calculer la moyenne pour chaque note
        $notesWithAverage = [];
        foreach ($notes as $note) {
            $noteId = $note->getId();
            $pdo = $database->getConnection();
            $moyenne = $database->getMainNoteAverage($noteId, $pdo);
            $notesWithAverage[] = [
                'note' => $note,
                'moyenne' => $moyenne
            ];
        }


    } else {
        $studentName = "Étudiant introuvable";
    }
}

$underNotesData = $database->getUnderNotes($studentId); // Récupère les sous-notes groupées par NoteID
$underNotes = [];

// Transformer les données des sous-notes en objets Sous_Note
foreach ($underNotesData as $noteId => $sousNotes) {
    if (!is_array($sousNotes)) {
        continue; // Vérifie que $sousNotes est un tableau
    }
    foreach ($sousNotes as $sousNote) {
        // Vérifiez que $sousNote est bien un objet de type Sous_Note
        if ($sousNote instanceof Sous_Note) {
            $underNotes[$noteId][] = $sousNote;
        }
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
                <th>Note /20</th>
                <th>Coefficient</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($notesWithAverage as $noteData) : ?>
                <?php $note = $noteData['note']; ?>
                <?php $moyenne = $noteData['moyenne']; ?>
                <tr>
                    <td><?= htmlspecialchars($note->getId()); ?></td>
                    <td>
            <textarea name="sujet_<?= htmlspecialchars($note->getId()); ?>" rows="1" disabled>
                <?= htmlspecialchars($note->getSujet()); ?>
            </textarea>
                    </td>
                    <td>
                        <?php if ($moyenne !== null): ?>
                            <?= number_format($moyenne, 2); ?>
                        <?php else: ?>
                            Aucune sous-note
                        <?php endif; ?>
                    </td>
                    <td><input type="number" name="coeff_<?= htmlspecialchars($note->getId()); ?>" value="<?= htmlspecialchars($note->getCoeff()); ?>" disabled></td>
                    <td>
                        <form method="POST" action="Professor.php" style="display:inline;">
                            <input type="hidden" name="action" value="delete_note">
                            <input type="hidden" name="note_id" value="<?= htmlspecialchars($note->getId()); ?>">
                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId); ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                        <button type="button" onclick="showUnderTable(this, 'desc<?= $note->getId(); ?>')">Afficher Détails</button>
                    </td>


                </tr>
                <input type="hidden" name="note_id[]" value="<?= htmlspecialchars($note->getId()); ?>">


                <?php if (!empty($underNotes[$note->getId()]) || true) : ?>
                    <tr id="desc<?= $note->getId(); ?>" class="idUnderTable" style="display: none;">
                        <td colspan="5">
                            <table width="100%">
                                <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Note</th>
                                    <th>Actions</th>

                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($underNotes[$note->getId()])) : ?>
                                    <?php foreach ($underNotes[$note->getId()] as $sousNote) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sousNote->getDescription()); ?></td>
                                            <td><?= htmlspecialchars($sousNote->getNote()); ?></td>
                                            <td>
                                                <form method="POST" action="Professor.php" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_under_note">
                                                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($studentId); ?>">
                                                    <input type="hidden" name="under_note_id" value="<?= htmlspecialchars($sousNote->getId()); ?>">
                                                    <button type="submit">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: gray;">
                                            Aucune sous-note disponible
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                            <button type="button" data-note-id="<?= $note->getId(); ?>" onclick="addUnderNoteRow(this)">Ajouter une ligne</button>
                        </td>
                    </tr>
                <?php endif; ?>


            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($studentId) : ?>
            <div style="margin-bottom: 10px;">
                <button type="button" onclick="addNoteRow()">Ajouter une note</button>
            </div>
        <?php endif; ?>
    </div>

</form>
