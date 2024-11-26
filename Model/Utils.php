<?php
// Définit le fuseau horaire par défaut sur "Europe/Paris"
date_default_timezone_set('Europe/Paris');

/**
 * Formate un timestamp en une chaîne lisible, affichant "Today", "Yesterday" ou la date complète.
 *
 * @param string $timestamp Le timestamp à formater (format reconnu par DateTime, par exemple "2024-04-25 14:30:00").
 * @return string La chaîne formatée représentant la date et l'heure.
 */
function formatTimestamp($timestamp): string
{
    // Crée un objet DateTime à partir du timestamp fourni
    $date = new DateTime($timestamp);

    // Crée un objet DateTime pour la date et l'heure actuelles
    $now = new DateTime();

    // Crée un objet DateTime pour la date d'hier
    $yesterday = new DateTime('yesterday');

    // Compare uniquement les dates (sans l'heure) du message et de la date actuelle
    if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
        // Si le message a été envoyé aujourd'hui, retourne "Today" suivi de l'heure
        return 'Today ' . $date->format('H:i');
    }
    // Compare la date du message avec celle d'hier
    elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
        // Si le message a été envoyé hier, retourne "Yesterday" suivi de l'heure
        return 'Yesterday ' . $date->format('H:i');
    } else {
        // Sinon, retourne la date complète au format jour.mois.année heure:minutes
        return $date->format('d.m.Y H:i');
    }
}
