<?php
require_once '../Model/Database.php';
require_once '../Model/Person.php';
require_once '../Model/Note.php';
require_once '../Model/UnderNote.php';

global $database;
$database = Database::getInstance();
$notes = [];
$studentName = "Sélectionnez un étudiant";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Création des lignes principales si un étudiant est sélectionné
    if (isset($_POST['student_id']) && !isset($_POST['sliders'])) {
        $studentId = (int)$_POST['student_id'];
        $database->createMainNotesForStudent($studentId);

        // Rediriger pour éviter un double envoi de formulaire
        header("Location: Professor.php?student_id=$studentId");
        exit;
    }

    // Enregistrement des curseurs
    if (isset($_POST['sliders'])) {
        $studentId = (int)$_POST['student_id']; // Récupérer l'ID de l'étudiant
        foreach ($_POST['sliders'] as $noteId => $sliders) {
            foreach ($sliders as $description => $value) {
                $database->saveSliderValue((int)$noteId, $description, (int)$value);
            }
        }

        // Redirection après enregistrement
        header("Location: Professor.php?student_id=$studentId");
        exit;
    }
}

// Gérer le cas où un étudiant est sélectionné via la redirection
$studentId = $_POST['student_id'] ?? ($_GET['student_id'] ?? null);

if ($studentId) {
    $student = $database->getUserById((int)$studentId);
    if ($student) {
        $studentName = htmlspecialchars($student['prenom']) . ' ' . htmlspecialchars($student['nom']);
        $notes = $database->getNotes((int)$studentId);
    } else {
        $studentName = "Étudiant introuvable";
        $notes = [];
    }
}


// Récupérer les valeurs des curseurs pour chaque note
foreach ($notes as $data) {
    $noteId = $data->getId();
    $sliderValues = $database->getSliderValues($noteId);

    // Créer un tableau associatif pour les descriptions et leurs valeurs
    $sliderValuesMap = [];
    foreach ($sliderValues as $slider) {
        $sliderValuesMap[$slider['description']] = $slider['note'];
    }
}

// Récupérer les moyennes pondérées des notes principales
$noteAverages = []; // Tableau pour stocker les moyennes

foreach ($notes as $data) {
    $noteId = $data->getId();
    $noteAverage = $database->getMainNoteAverage($noteId); // Appel à la méthode
    $noteAverages[$noteId] = $noteAverage;
}



?>
<body>


<h2 id="selected-student-name"><?= $studentName ?></h2>
<form id="noteForm" action="Professor.php" method="post">
    <input type="hidden" id="student-id" name="student_id" value="<?= htmlspecialchars($studentId ?? '') ?>">


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
            $noteId  = $data->getId();
            $sujet   = $data->getSujet();
            $noteVal = $data->getNote();
            $coeff   = $data->getCoeff();
            $sliderValues = $database->getSliderValues($noteId);
            $average = $noteAverages[$noteId] ?? null;

            // Créer un tableau associatif pour stocker les valeurs des curseurs
            $sliderValuesMap = [];
            foreach ($sliderValues as $slider) {
                $sliderValuesMap[$slider['description']] = $slider['note'];
            }
            ?>
            <!-- Ligne principale -->
            <tr>
                <td><?= $noteId ?></td>
                <td>
                    <?= htmlspecialchars($sujet) ?>
                </td>
                <td><?= $average !== null ? number_format($average, 2) : 'N/A' ?></td>
                <td><?= number_format($coeff) ?></td>
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
                                                value="<?= htmlspecialchars($sliderValuesMap['observation'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][observation]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['raisonnement'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][raisonnement]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['pratique'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][pratique]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['efficacite'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][efficacite]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['qualite'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][qualite]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['initiative'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][initiative]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['autonomie'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][autonomie]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['rapports'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][rapports]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['ponctualite'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][ponctualite]"
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
                                                value="<?= htmlspecialchars($sliderValuesMap['interet'] ?? 0) ?>"
                                                name="sliders[<?= $noteId ?>][interet]"
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
    <button type="submit">Enregistrer la note</button>
</form>
<div id="confirmationModal" class="modal">
    <div class="modal-content">
        <p>Êtes-vous sûr de vouloir enregistrer les notes ?</p>
        <button id="confirmButton" class="modal-button">Confirmer</button>
        <button id="cancelButton" class="modal-button cancel">Annuler</button>
    </div>
</div>
</body>
