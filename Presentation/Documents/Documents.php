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
require_once dirname(__FILE__) . '/../../Model/Offer.php';
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id']; // ID of the connected student

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $name = $_FILES['files']['name'][$index];
            $size = $_FILES['files']['size'][$index];
            $error = $_FILES['files']['error'][$index];

            if ($error === UPLOAD_ERR_OK) {
                // Check the file extension
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION)); // Get the extension
                if (!in_array($extension, $allowedExtensions)) {
                    echo "Le fichier $name n'est pas autorisé. Seules les images et les PDF sont acceptés.<br>";
                    continue;
                }

                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filePath = $uploadDir . uniqid() . '-' . basename($name);

                if ($db->fileExists($name, $userId)) {
                    continue; // Skip file if already existing
                }
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Add the file to the database
                    $db->addFile($name, $filePath, $userId, $size);
                }
            }
        }
    }

    // Manage file deletion
    if (!empty($_POST['fileId'])) {
        $db->deleteFile((int)$_POST['fileId']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Retrieve files to view them
$files = $db->getFiles($userId);
?>

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
                <form method="get" action="Download.php">
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