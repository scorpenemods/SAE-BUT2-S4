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

<body>

<?php
// Tableau statique des 4 notes
$notes = [
    [
        'id'    => 1,
        'sujet' => 'A',
        'note'  => 12.00,
        'coeff' => 4
    ],
    [
        'id'    => 2,
        'sujet' => 'B',
        'note'  => 5.00,
        'coeff' => 2
    ],
    [
        'id'    => 3,
        'sujet' => 'C',
        'note'  => 15.00,
        'coeff' => 3
    ],
    [
        'id'    => 4,
        'sujet' => 'D',
        'note'  => 8.75,
        'coeff' => 1
    ],
];
?>


<input type="hidden" id="student-id" name="student_id" value="">
<h2 id="selected-student-name">Sélectionnez un étudiant</h2>
<form id="noteForm" action="#" method="post">
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

        <?php foreach ($notes as $data): ?>
            <?php
            $noteId  = $data['id'];
            $sujet   = $data['sujet'];
            $noteVal = $data['note'];
            $coeff   = $data['coeff'];
            ?>
            <!-- Ligne principale -->
            <tr>
                <td><?= $noteId ?></td>
                <td>
                    <?= htmlspecialchars($sujet) ?>
                </td>
                <td><?= number_format($noteVal, 2) ?></td>
                <td>
                    <input type="number" value="<?= $coeff ?>" disabled>
                </td>
                <td>
                    <button
                            type="button"
                            onclick="showUnderTable(this, 'desc<?= $noteId ?>')"
                    >
                        Afficher Détails
                    </button>
                </td>
            </tr>

            <!-- Sous-ligne, cachée par défaut -->
            <tr id="desc<?= $noteId ?>" class="idUnderTable" style="display: none;">
                <td colspan="5">
                    <!-- ================================
                         Sous-tableaux (sliders)
                         ================================ -->

                    <!-- 1) Aptitudes intellectuelles -->
                    <table>
                        <thead>
                        <tr>
                            <th>Aptitudes intellectuelles</th>
                            <th>Échelle (0 à 5)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Sens de l'observation</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-obs-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Qualité du raisonnement logique</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-rais-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Sens pratique</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-prat-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- 2) Qualités opérationnelles -->
                    <table>
                        <thead>
                        <tr>
                            <th>Qualités opérationnelles</th>
                            <th>Échelle (0 à 5)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Efficacité, respect des délais</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-eff-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Sens de la qualité</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-qual-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Esprit d'initiative</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-ini-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Autonomie</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-auto-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- 3) Relationnel et comportement -->
                    <table>
                        <thead>
                        <tr>
                            <th>Relationnel et comportement</th>
                            <th>Échelle (0 à 5)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Sens des rapports humains</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-rel-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Ponctualité, assiduité</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-ponc-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Intérêt porté par le sujet</td>
                            <td>
                                <div class="slider-container">
                                    <div class="slider-wrapper">
                                        <input
                                                type="range"
                                                min="0" max="5" step="1"
                                                value="0"
                                                oninput="updateValue('slider-value-int-<?= $noteId ?>', this.value)"
                                        >
                                        <div class="ticks">
                                            <div class="tick">0</div>
                                            <div class="tick">1</div>
                                            <div class="tick">2</div>
                                            <div class="tick">3</div>
                                            <div class="tick">4</div>
                                            <div class="tick">5</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>
</form>
</body>
