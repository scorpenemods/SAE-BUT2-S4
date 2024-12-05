<?php

// Générer un jeton CSRF si ce n'est pas déjà fait
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour voir vos fichiers.";
    exit;
}

// Initialiser la base de données
$db = Database::getInstance();
$userId = $_SESSION['user_id']; // ID de l'étudiant connecté

// Gérer le téléversement des fichiers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    if (!empty($_FILES['files'])) {
        foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
            $name = $_FILES['files']['name'][$index];
            $size = $_FILES['files']['size'][$index];
            $error = $_FILES['files']['error'][$index];

            if ($error === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filePath = $uploadDir . uniqid() . '-' . basename($name);
                if ($db->fileExists($name, $userId)) {
                    continue; // Ignore le fichier si déjà existant
                }
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Ajouter le fichier dans la base de données
                    $db->addFile($name, $filePath, $senderId ?? 0, $size);
                }
            }
        }
    }

    // Gérer la suppression des fichiers
    if (!empty($_POST['fileId'])) {
        $db->deleteFile((int)$_POST['fileId']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Récupérer les fichiers pour les afficher
$files = $db->getFiles($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <link rel="stylesheet" href="Documents.css">
</head>
<body>
<form class="box" method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="box__input">
        <label id="file-label" for="file">Aucun fichier choisi</label>
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