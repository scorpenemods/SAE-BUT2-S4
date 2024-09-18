<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer de numéro de téléphone</title>
    <link rel="stylesheet" href="ChangePhoneNumber.css">
</head>
<body>
<div class="ChangePhone-container">
    <h2>Changer de numéro de téléphone</h2>
    <form action="../Redirection/RedirectionInformationAccount.php" method="POST">
        <div class="form-group">
            <label for="phone" id="phone" name="phone" required</label>
            <input type="tel" id="phone" name="phone" placeholder="Nouveau numéro de téléphone" required><br>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-number">Confirmer</button>

        </div>
    </form>
</div>
</body>
</html>

<?php
function isValidPhoneNumber($phoneNumber) {
// Vérifie si le numéro de téléphone contient exactement 10 chiffres
return preg_match('/^\d{10}$/', $phoneNumber);
}

$phoneNumber = $_POST['phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isValidPhoneNumber($phoneNumber)) {
        echo "Numéro de téléphone invalide. Veuillez entrer un numéro de téléphone à 10 chiffres.";
    }
}
?>