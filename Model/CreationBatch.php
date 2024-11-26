<?php
// Inclut la classe Database pour gérer les interactions avec la base de données.
include_once("Database.php");

// Active l'affichage des erreurs pour le débogage.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Génère un mot de passe aléatoire de 10 caractères.
function PasswordGenerator() : String {
    $Chaine  = "abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $Chaine = str_shuffle($Chaine); // Mélange les caractères
    $Chaine = substr($Chaine, 0, 10); // Prend les 10 premiers caractères
    return $Chaine;
}

// Importe des utilisateurs à partir d'un fichier CSV.
function importCsv($filePath): void
{
    $db = Database::getInstance(); // Récupère une instance unique de la base de données.
    if (empty($filePath)) {
        echo "Chemin Invalide.<br>";
        return; // Arrête la fonction si le chemin est vide.
    }
    echo "Début de l'importation...<br>";
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        fgetcsv($handle, null, ";"); // Ignore la première ligne (entêtes du CSV).
        while (($data = fgetcsv($handle, null, ";")) !== FALSE) {
            if (count($data) < 6) {
                echo "Nombre de champs incorrect sur cette ligne.<br>";
                continue; // Passe à la ligne suivante si des champs sont manquants.
            }

            // Associe les colonnes CSV aux variables correspondantes.
            list($nom, $prenom, $email, $role, $activite, $telephone) = $data;
            $telephone = !empty($telephone) ? $telephone : null; // Permet des valeurs nulles pour le téléphone.

            // Ajoute un utilisateur à la base avec les données du CSV.
            $db->addUser($email, PasswordGenerator(), $telephone, $prenom, $activite, (int)$role, $nom, 1);
        }
        fclose($handle); // Ferme le fichier après traitement.
        echo "Import du fichier CSV terminé avec succès.<br>";
    } else {
        echo "Impossible d'ouvrir ce fichier CSV.<br>";
    }
}
