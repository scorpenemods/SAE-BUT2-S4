<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    if ($password1 !== $password2) {
        $error_message = "Les mots de passe ne correspondent pas.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="ForgotPasswordCode.css">
</head>
<body>
<div class="ForgotPassword-container">
    <h2>Création du nouveau mot de passe</h2>
    <?php if (isset($error_message)) : ?>
        <p style="color:red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="password-container">
            <input type="password" id="password1" name="password1" placeholder="Nouveau mot de passe" required>
            <img src="eye1.jpeg" id="eye" onclick="togglePasswordVisibility()" width="5%">
        </div>
        <div class="password-container">
            <input type="password" id="password2" name="password2" placeholder="Confirmer mot de passe" required>
            <img src="eye1.jpeg" id="eye" onclick="togglePasswordVisibility()" width="5%">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-password">Confirmer</button>
        </div>
    </form>
    <script>
        let isPasswordVisible = false;
        function togglePasswordVisibility() {
            const passwordFields = document.querySelectorAll('.password-container input[type="password"]');
            const eyeIcons = document.querySelectorAll('.password-container img');
            passwordFields.forEach((passwordField, index) => {
                if (isPasswordVisible) {
                    passwordField.setAttribute('type', 'password');
                    eyeIcons[index].src = 'eye1.jpeg';
                } else {
                    passwordField.setAttribute('type', 'text');
                    eyeIcons[index].src = 'eye2.jpeg';
                }
            });
            isPasswordVisible = !isPasswordVisible;
        }
    </script>
</div>
</body>
</html>
