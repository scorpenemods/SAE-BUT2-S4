<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirection</title>
    <link rel="stylesheet" href="Redirection.css"> <!-- Lien vers le fichier CSS -->
</head>
<body>
<div class="container">
    <h1>Redirection...</h1>
    <p>Retour aux informations du compte dans 3 secondes...</p>
</div>

<script>
    // JavaScript redirige vers les informations du compte après 3 seconds
    setTimeout(function() {
        window.location.href = "../InformationAccount/Information.php";
    }, 3000);
</script>
</body>
</html>

