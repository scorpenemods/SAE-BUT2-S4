<?php
//Tableau ( [id] => 3 [user_id] => 1 [duration] => 2 [address] => [study_level] => Bac+2 [salary] => [begin_date] => )
//    public function addNotification(int $userId, string $content, string $type): bool

function sendNotification($offer){
    $database = (Database::getInstance());
    $alerts = $database->getAlert();

    foreach ($alerts as $alert) {
        $params = [];
        $user_id = $alert['user_id'];

        if ($alert['duration'] !== '') {
            $params['duration'] = $alert['duration'];
        }
        if ($alert['address'] !== '') {
            $params['address'] = $alert['address'];
        }
        if ($alert['study_level'] !== '') {
            $params['study_level'] = $alert['study_level'];
        }
        if ($alert['salary'] !== '') {
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
            if (!($value == $value2)){
                $comparaison = false;
            }
        }

        if ($comparaison) {
            $database->addNotification($user_id, "une offre correspond", "alerte d'offre");
        }

    }




}

