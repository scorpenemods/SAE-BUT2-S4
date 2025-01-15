<?php

class FileHandler
{
    private $db;
    private $allowedExtensions;
    private $uploadDir;

    public function __construct($db, $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'], $uploadDir = '../uploads/')
    {
        $this->db = $db;
        $this->allowedExtensions = $allowedExtensions;
        $this->uploadDir = $uploadDir;

        // Crée le dossier d'upload si nécessaire
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function uploadFiles($userId, $files): void
    {
        foreach ($files['tmp_name'] as $index => $tmpName) {
            $name = $files['name'][$index];
            $size = $files['size'][$index];
            $error = $files['error'][$index];

            if ($error === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($extension, $this->allowedExtensions)) {
                    echo "Le fichier $name n'est pas autorisé. Seules les images et les PDF sont acceptés.<br>";
                    continue;
                }

                $filePath = $this->uploadDir . uniqid() . '-' . basename($name);

                // Vérifie si le fichier existe déjà
                if ($this->db->fileExists($name, $userId)) {
                    echo "Le fichier $name existe déjà.<br>";
                    continue;
                }

                // Déplace le fichier et l'ajoute à la base de données
                if (move_uploaded_file($tmpName, $filePath)) {
                    $this->db->addFile($name, $filePath, $userId, $size);
                    echo "Le fichier $name a été téléchargé avec succès.<br>";
                } else {
                    echo "Erreur lors du téléchargement du fichier $name.<br>";
                }
            }
        }
    }

    public function deleteFile($userId, $fileId): void
    {
        // Vérifie si l'utilisateur possède le fichier avant de le supprimer
        $files = $this->db->getFiles($userId);
        foreach ($files as $file) {
            if ($file['id'] === $fileId) {
                $this->db->deleteFile($fileId);
                echo "Le fichier a été supprimé avec succès.<br>";
                return;
            }
        }

        echo "Le fichier n'a pas été trouvé ou vous n'êtes pas autorisé à le supprimer.<br>";
    }

    public function getFiles($userId): array
    {
        return $this->db->getFiles($userId);
    }
}
