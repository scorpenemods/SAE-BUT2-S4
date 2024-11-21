<?php

function sendNotification($offer): void
{
    $database = (Database::getInstance());
    $alerts = $database->getAlert();

    foreach ($alerts as $alert) {
        $params = [];
        $user_id = $alert['user_id'];

        if ($alert['duration'] !== 0) {
            $params['duration'] = $alert['duration'];
        }
        if ($alert['address'] !== '') {
            $params['address'] = $alert['address'];
        }
        if ($alert['study_level'] !== '') {
            $params['study_level'] = $alert['study_level'];
        }
        if ($alert['salary'] !== 0) {
            $params['salary'] = $alert['salary'];
        }
        if ($alert['begin_date'] !== '') {
            $params['begin_date'] = $alert['begin_date'];
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
            $database->addNotification($user_id, "une offre correspond", "alerte d'offre");
        }

    }

}

