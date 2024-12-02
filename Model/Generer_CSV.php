<?php

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="modele_utilisateurs.xlsx"');

// Colonnes du fichier CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['Nom', 'Prenom', 'Email', 'Role entre 1 et 4','Activité','Téléphone']);
fclose($output);
exit;