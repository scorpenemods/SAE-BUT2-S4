
<?php
#page qui permet au secrétariat de valider les comptes

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title</title>
    <link rel="stylesheet" href="AccountValidation.css">
</head>
<body>
<div class="account-container">
<h1>Récapitulatif du compte</h1>
    <form action="" method="POST">
        <div class="form-group">

            <h2>Nom</h2>
                <p></p>

            <h2>Prénom :</h2>
                <p></p>

            <h2>Téléphone :</h2>
                <p></p>

            <h2>Email :</h2>
                <p></p>

            <h2>Rôle :</h2>
                <p></p>

            <h2>Activité professionel/Universitarie : </h2>
                <p></p>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-validate">Valider</button>
            <button type="submit" class="btn-refuse">Refuser</button>
        </div>
    </form>
</div>
</body>
</html>