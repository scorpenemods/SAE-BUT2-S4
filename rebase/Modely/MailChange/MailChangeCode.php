<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="MailChangeCode.css">
</head>
<body>
<div class="ForgotPassword-container">
    <h2>Code de récupération</h2>
    <form action="" method="POST">
        <div class="form-group code-input-container">
            <label for="code" id="code" name="code" required </label>
            <input type="text" name="code[]" maxlength="1" required>
            <input type="text" name="code[]" maxlength="1" required>
            <input type="text" name="code[]" maxlength="1" required>
            <input type="text" name="code[]" maxlength="1" required>
            <input type="text" name="code[]" maxlength="1" required><br>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-password">Confirmer</button>
        </div>
    </form>
</body>
<script src="MailChangeCode.js"></script>
</html>


