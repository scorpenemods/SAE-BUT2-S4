<?php

require_once "../Model/Database.php";
// Instanciation de l'objet Database (singleton pour une seule instance de connexion)
$database = (Database::getInstance());
$missions = $database->getInputsPreAgreementForm(6);
$inputs = json_decode($missions['inputs'], true);


function getFieldValue($field, $inputs = null, $default = 'Pas défini'): string
{
    if (isset($inputs[$field])) {
        return htmlspecialchars($inputs[$field]); // Priorité aux valeurs de $inputs
    }
    if ($default !== null) {
        return htmlspecialchars($default); // Sinon, utilise la valeur par défaut
    }
    return ''; // Sinon, champ vide
}

echo '<br>';
echo getFieldValue('intershipSubject', $inputs);    
echo '<br>';
echo getFieldValue('tasksFunctions', $inputs);

?>
