<?php
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

// Generate a CSRF token if not already done
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour voir vos fichiers.";
    exit;
}

// Initialize the database
$db = Database::getInstance();
$userId = $_SESSION['user_id']; // ID of the connected student

function handleFileUpload($db, $userId): void
{
    global $allowedExtensions;

    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $name = $_FILES['files']['name'][$index];
            $size = $_FILES['files']['size'][$index];
            $error = $_FILES['files']['error'][$index];

            if ($error === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    echo "Le fichier $name n'est pas autorisé. Seules les images et les PDF sont acceptés.<br>";
                    continue;
                }

                $uploadDir = '../Presentation/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filePath = $uploadDir . uniqid() . '-' . basename($name);

                if ($db->fileExists($name, $userId)) {
                    continue; // Skip file if already existing
                }

                if (move_uploaded_file($tmpName, $filePath)) {
                    $db->addFile($name, $filePath, $userId, $size);
                }
            }
        }
    }
}
function handleRapportUpload($db, $userId, $groupId): void
{
    global $allowedExtensions;

    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $name = $_FILES['files']['name'][$index];
            $size = $_FILES['files']['size'][$index];
            $error = $_FILES['files']['error'][$index];

            if ($error === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    echo "Le fichier $name n'est pas autorisé. Seules les images et les PDF sont acceptés.<br>";
                    continue;
                }

                $uploadDir = '../Presentation/RapportDeStage/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filePath = $uploadDir . uniqid() . '-' . basename($name);

                if ($db->fileExists($name, $userId)) {
                    continue; // Skip file if already existing
                }

                if (move_uploaded_file($tmpName, $filePath)) {
                    $db->addLivretFile($name, $filePath, $userId, $size, $groupId);
                }
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    // Gestion des fichiers du Code 1
    if ($_POST['form_id'] === 'uploader_fichier' || $_POST['form_id'] === 'delete_file') {
        if ($_POST['form_id'] === 'uploader_fichier') {
            handleFileUpload($db, $userId);
        } elseif ($_POST['form_id'] === 'delete_file') {
            // Suppression d'un fichier (Code 1)
            if (!empty($_POST['fileId'])) {
                $fileId = (int)$_POST['fileId'];
                $db->deleteFile($fileId);
            }
        }
    }

    // Gestion des rapports du Code 2
    if ($_POST['form_id'] === 'uploader_rapport' || $_POST['form_id'] === 'delete_rapport') {
        if ($_POST['form_id'] === 'uploader_rapport') {

            $groupId = $_SESSION['group_id'];

            handleRapportUpload($db, $userId, $groupId);
        } elseif ($_POST['form_id'] === 'delete_rapport') {
            // Suppression d'un rapport (Code 2)
            if (!empty($_POST['fileId'])) {
                $fileId = (int)$_POST['fileId'];
                $db->deleteFile($fileId);
            }
        }
    }

    // Redirection pour éviter la soumission multiple
    header("Location: " . $_SERVER['PHP_SELF']);

}

$files = $db->getFiles($userId);

?>
<html lang="FR">
<body>
<form class="box" method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="box__input">
        <input type="file" name="files[]" id="file" multiple>
        <button class="box__button" type="submit">Uploader</button>
    </div>
    <div class="box__uploading">Envoi en cours...</div>
    <div class="box__success">Upload terminé !</div>
    <div class="box__error">Erreur : <span></span></div>
</form>

<div class="file-list">
    <h2>Fichiers Uploadés</h2>
    <div class="file-grid">
        <?php foreach ($files as $file): ?>
            <div class="file-card">
                <div class="file-info">
                    <strong><?= htmlspecialchars($file['name']) ?></strong>
                    <p><?= round($file['size'] / 1024, 2) ?> KB</p>
                </div>
                <form method="get" action="Documents/Download.php">
                    <input type="hidden" name="file" value="<?= htmlspecialchars($file['path']) ?>">
                    <button type="submit" class="download-button">Télécharger</button>
                </form>
                <form method="post" action="" class="delete-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="fileId" value="<?= $file['id'] ?>">
                    <button type="submit" class="delete-button">Supprimer</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>