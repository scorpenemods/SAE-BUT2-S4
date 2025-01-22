<?php
// Manage to reset notification when readed
session_start();
header('Content-Type: application/json; charset=utf-8');

// Mettre à jour le temps de la dernière vérification
$_SESSION['last_check_time'] = date('Y-m-d H:i:s');

echo json_encode(['status' => 'success']);
?>
