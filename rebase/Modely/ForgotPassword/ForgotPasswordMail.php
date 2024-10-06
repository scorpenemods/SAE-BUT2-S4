<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="ForgotPswdStyles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha256-DIym3sfZPMYqRkk8oWhZyEEo9xYKpIZo2Vafz3tbv94=" crossorigin="anonymous" />
</head>
<body>
<div class="container">
    <h2>Réinitialisation du mot de passe</h2>
    <?php if (!empty($error)) { ?>
        <div class="notification error-notification">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
    <?php } elseif (!empty($success)) { ?>
        <div class="notification success-notification">
            <i class="fas fa-check-circle"></i>
            <?php echo $success; ?>
        </div>
    <?php } ?>
    <form action="ForgotPassword.php" method="POST">
        <div class="form-group">
            <label for="email">Adresse email :</label>
            <div class="input-icon">
                <input type="email" id="email" name="email" placeholder="Entrez votre adresse email" required>
                <i class="fas fa-envelope"></i>
            </div>
        </div>
        <button type="submit" class="btn">Envoyer le code de vérification</button>
    </form>
    <div class="link">
        <a href="../../../Index.php">Retour à la connexion</a>
    </div>
</div>
<script>
    setTimeout(function() {
        var notification = document.querySelector('.notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 5000); // Hide notification in 5 sec
</script>
</body>
</html>