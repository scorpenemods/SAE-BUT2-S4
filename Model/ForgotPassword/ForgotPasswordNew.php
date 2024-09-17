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
    <link rel="stylesheet" href="">
</head>
<body>
<div class="ForgotPassword-container">
    <h2>Création du nouveau mot de passe</h2>
    <?php if (isset($error_message)) : ?>
        <p style="color:red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <input type="password" id="password1" name="password1" placeholder="Nouveau mot de passe" required><br>
            <img src="eye1.jpeg" id="eye" onclick="togglePasswordVisibility()" width= "5%">
            <input type="password" id="password2" name="password2" placeholder="Confirmer mot de passe" required><br>
            <button type="submit" class="btn-password">Confirmer</button>
        </div>
    </form>
    <script>
        let isPasswordVisible = false;
        function togglePasswordVisibility() {
            const passwordField1 = document.getElementById('password1');
            const passwordField2 = document.getElementById('password2');
            const eyeIcon = document.getElementById('eye');
            if (isPasswordVisible) {
                passwordField1.setAttribute('type', 'password');
                passwordField2.setAttribute('type', 'password');
                eyeIcon.src = 'eye1.jpeg';
            } else {
                passwordField1.setAttribute('type', 'text');
                passwordField2.setAttribute('type', 'text');
                eyeIcon.src = 'eye2.jpeg';
            }
            isPasswordVisible = !isPasswordVisible;
        }
    </script>
</div>
</body>
</html>
