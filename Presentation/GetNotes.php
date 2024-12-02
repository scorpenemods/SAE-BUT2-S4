<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
global $database;
session_start();

$person = unserialize($_SESSION['user']);
$userId = $person->getId();

$database = Database::getInstance();
$pdo = $database->getConnection();

// Affichage des notes pour un étudiant
if (isset($_GET['student_id'])) {
    $studentId = intval($_GET['student_id']);
    $notes = $database->getStudentNotes($studentId);
    // Permet d'ajouter les notes à la base de données
    if (isset($_POST['submit_notes']) && ($_POST['student_id'])) {
        if (isset($_POST['sujet'], $_POST['appreciations'], $_POST['note'], $_POST['coeff'])) {
            $allFieldsFilled = true;
            $notesData = [];

            foreach ($_POST['sujet'] as $index => $sujet) {
                if (empty($_POST['sujet'][$index]) || empty($_POST['appreciations'][$index]) || empty($_POST['note'][$index]) || empty($_POST['coeff'][$index])) {
                    $allFieldsFilled = false;
                    break;
                }
                $notesData[] = [
                    'sujet' => $_POST['sujet'][$index],
                    'appreciation' => $_POST['appreciations'][$index],
                    'note' => $_POST['note'][$index],
                    'coeff' => $_POST['coeff'][$index],
                ];
            }

            if ($allFieldsFilled) {
                try {
                    $database->addNotes($studentId, $notesData, $pdo);
                    header("Location: Professor.php");
                    exit();
                } catch (PDOException $e) {
                    echo "Erreur lors de l'ajout des notes : " . $e->getMessage();
                }
            } else {
                echo "Veuillez remplir tous les champs.";
            }
        } else {
            echo "Erreur lors de la soumission du formulaire. Veuillez réessayer.";
        }
    }


    // Permet de supprimer une note dans la base de données
    if (isset($_POST['delete_note'])) {
        if (!empty($_POST['note_id'])) {
            $noteId = intval($_POST['note_id']);

            $database = Database::getInstance();
            $pdo = $database->getConnection();

            try {
                $database->deleteNote($noteId, $userId, $pdo);
                header("Location: Professor.php");
                exit();
            } catch (PDOException $e) {
                echo "Erreur lors de la suppression de la note : " . $e->getMessage();
            }
        } else {
            echo "ID de la note manquant.";
        }
    }

    //Sauvegarder les notes
    if (isset($_POST['saveNote'])) {
        if (isset($_POST['note_id'], $_POST['sujet'], $_POST['appreciations'], $_POST['note'], $_POST['coeff'])) {
            $noteId = $_POST['note_id'];
            $sujet = $_POST['sujet'];
            $appreciation = $_POST['appreciations'];
            $note = $_POST['note'];
            $coeff = $_POST['coeff'];

            // Vérifier si aucun des champs n'est vide
            if (!empty($sujet) && !empty($appreciation) && is_numeric($note) && is_numeric($coeff)) {
                try {
                    // Mise à jour de la note en utilisant la méthode updateNote
                    $database->updateNote(
                        $noteId,
                        $userId,
                        $sujet,
                        $appreciation,
                        $note,
                        $coeff,
                        $pdo
                    );

                    // Renvoyer une réponse de succès en texte simple
                    echo "success";
                    exit();
                } catch (PDOException $e) {
                    // Renvoyer une réponse d'erreur en texte simple
                    echo "Erreur lors de la mise à jour des notes : " . $e->getMessage();
                    exit();
                }
            } else {
                // Renvoyer une réponse d'erreur si les champs ne sont pas valides
                echo "Veuillez remplir tous les champs correctement.";
                exit();
            }
        } else {
            // Renvoyer une réponse d'erreur si le formulaire est mal soumis
            echo "Erreur lors de la soumission du formulaire. Veuillez réessayer.";
            exit();
        }
    }

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



    foreach ($notes as $note) {
        echo "<tr id='row_{$note['id']}'>";
        echo "<form method='post' action='GetNotes.php' onsubmit='event.preventDefault(); return false;'>";
        echo "<td hidden='hidden'>{$note['id']}</td>";
        echo "<td><textarea name='sujet' disabled>{$note['sujet']}</textarea></td>";
        echo "<td><textarea name='appreciations' disabled>{$note['appreciation']}</textarea></td>";
        echo "<td><input type='number' name='note' value='{$note['note']}' disabled></td>";
        echo "<td><input type='number' name='coeff' value='{$note['coeff']}' disabled></td>";
        echo "<td>
                <button type='button' id='edit_{$note['id']}' onclick='editOrSave({$note['id']})' >Modifier les notes</button>
                <button type='button' id='delete_{$note['id']}' onclick='showConfirmation({$note['id']}, {$studentId})' disabled>Supprimer</button>
              </td>";
        echo "</form>";
        echo "</tr>";
    }
}
?>
<script src="../View/Principal/Principal.js"></script>
