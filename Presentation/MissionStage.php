<?php

require_once "../Model/Database.php";
// Instanciation de l'objet Database (singleton pour une seule instance de connexion)
$database = (Database::getInstance());


function getFieldValue($field, $inputs = null, $default = 'Pas défini'): string
{
    if (isset($inputs[$field])) {
        return htmlspecialchars($inputs[$field]); // Priorité aux valeurs de $inputs
    }
    if ($default !== null) {
        return htmlspecialchars($default); // Sinon, utilise la valeur par défaut
    }
    return ''; // Sinon, champ vide
}?>
<div style="display: flex; justify-content: center;" id="mission-details">
    <?php
    if (isset($_GET['stage_id'])) {
        $userId = intval($_GET['stage_id']);
        $group = $database->getUserGroupByIdUser($userId);
        $preAgreement = $database->getPreAgreementByIdGroup($group);
        if ($preAgreement !== null) { // Check if $preAgreement is valid
            print_r($database->getInputsPreAgreementForm($preAgreement));
            $missions = $database->getInputsPreAgreementForm($preAgreement);
            if ($missions && isset($missions['inputs'])) { // Check if $missions is valid
                $inputs = json_decode($missions['inputs'], true);
                try {
                    echo '<br>';
                    echo $userId;
                    echo getFieldValue('intershipSubject', $inputs);
                    echo '<br>';
                    echo getFieldValue('tasksFunctions', $inputs);
                } catch (ErrorException $e) {
                    echo 'Impossible de récupérer les données';
                }
            } else {
                echo 'Aucune mission trouvée pour cet accord préalable';
            }
        } else {
            echo 'Aucun accord préalable trouvé pour le groupe';
        }
    } else {
        echo 'Aucune variable stage_id';
    }
    ?>

</div>