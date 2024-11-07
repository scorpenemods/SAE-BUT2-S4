<?php
date_default_timezone_set('Europe/Paris');
function formatTimestamp($timestamp): string
{
    $date = new DateTime($timestamp); // Crée un objet DateTime à partir du timestamp
    $now = new DateTime(); // Crée un objet DateTime pour la date actuelle
    $yesterday = new DateTime('yesterday'); // Crée un objet DateTime pour la date d'hier

    // Compare la date du message avec la date d'aujourd'hui
    if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
        return 'Today ' . $date->format('H:i'); // Si c'est aujourd'hui, retourne "Today" avec l'heure
    }
    // Compare la date du message avec celle d'hier
    elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
        return 'Yesterday ' . $date->format('H:i'); // Si c'était hier, retourne "Yesterday" avec l'heure
    } else {
        return $date->format('d.m.Y H:i'); // Sinon, retourne la date complète au format jour/mois/année heure:minutes
    }
}