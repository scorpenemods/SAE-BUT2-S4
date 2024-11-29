<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
global $database;
session_start();

$person = unserialize($_SESSION['user']);
$userId = $person->getId();

$database = Database::getInstance();
$pdo = $database->getConnection();

// Supprimer une note
if (isset($_POST['delete_note'])) {
    if (!empty($_POST['note_id']) && !empty($_POST['student_id'])) {
        $noteId = intval($_POST['note_id']);
        $studentId = intval($_POST['student_id']);

        try {
            $database->deleteNote($noteId, $studentId, $pdo);
            echo "success";
        } catch (PDOException $e) {
            echo "Erreur lors de la suppression de la note : " . $e->getMessage();
        }
    } else {
        echo "ID de la note ou de l'étudiant manquant.";
    }
    exit();
}

// Sauvegarder une note modifiée



// Affichage des notes pour un étudiant
if (isset($_GET['student_id'])) {
    $studentId = intval($_GET['student_id']);
    $database = Database::getInstance();
    $notes = $database->getStudentNotes($studentId);

    foreach ($notes as $note) {
        echo "<tr id='row_{$note['id']}'>";
        echo "<form method='post' action='GetNotes.php' onsubmit='event.preventDefault(); return false;'>";
        echo "<td hidden='hidden'>{$note['id']}</td>";
        echo "<td><textarea name='sujet' disabled>{$note['sujet']}</textarea></td>";
        echo "<td><textarea name='appreciations' disabled>{$note['appreciation']}</textarea></td>";
        echo "<td><input type='number' name='note' value='{$note['note']}' disabled></td>";
        echo "<td><input type='number' name='coeff' value='{$note['coeff']}' disabled></td>";
        echo "<td>
                <button type='button' id='edit_{$note['id']}' onclick='editOrSave({$note['id']})' disabled>Modifier les notes</button>
                <button type='button' id='delete_{$note['id']}' onclick='showConfirmation({$note['id']}, {$studentId})' disabled>Supprimer</button>
              </td>";
        echo "</form>";
        echo "</tr>";
    }
}
?>
<script src="../View/Principal/Principal.js"></script>
