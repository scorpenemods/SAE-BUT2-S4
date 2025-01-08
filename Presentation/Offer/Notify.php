<?php
// File: Notify.php
// Notify users of new offers
require_once dirname(__FILE__) . '/../../Model/Offer.php';
$db = Database::getInstance()->getConnection();
function send_notification($offer): void
{
    $database = (Database::getInstance());
    $alerts = $database->getAlert();

    foreach ($alerts as $alert) {
        $params = [];
        $userId = $alert['userId'];

        if ($alert['duration'] !== 0) {
            $params['duration'] = $alert['duration'];
        }
        if ($alert['address'] !== '') {
            $params['address'] = $alert['address'];
        }
        if ($alert['studyLevel'] !== '') {
            $params['studyLevel'] = $alert['studyLevel'];
        }
        if ($alert['salary'] !== 0) {
            $params['salary'] = $alert['salary'];
        }
        if ($alert['beginDate'] !== '') {
            $params['beginDate'] = $alert['beginDate'];
        }

        $comparaison = true;
        foreach ($params as $key => $value) {
            $method = 'get' . ucfirst($key);
            if (method_exists($offer, $method)) {
                $value2 = $offer->$method();
            }
            if (isset($value2)) {
                if (!($value == $value2)){
                    $comparaison = false;
                }
            }
        }

        if ($comparaison) {
            $database->addNotification($userId, "une offre correspond", "alerte d'offre");
        }

    }

}

