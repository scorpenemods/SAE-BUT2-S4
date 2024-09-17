<?php
$pdo = new PDO("mysql:host=localhost;dbname=td7et8", 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);