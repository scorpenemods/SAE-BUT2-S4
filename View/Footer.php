<!-- affichage du footer -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/View/css/Footer.css">
</head>
<footer id="footer" class="<?php echo $darkModeEnabled ?? 0 ? 'dark-mode' : ''; ?>">
    <div class="container">
        <div class="footer-content">
            <div class="footer-row">
                <div class="logo-container">
                    <img src="../Resources/iut2.png" alt="Logo" class="logo">
                <nav class="nav-footer">
                    <a href="Presentation/InformationsFooter.php"><?= $translations['mentionLegalesInfos'] ?></a>
                    <a href="Presentation/Apropos.php"><?= $translations['propos'] ?></a>
                    <a href="Presentation/Confidentialité.php"><?= $translations['confid'] ?></a>
                    <a href="Presentation/conditions-utilisation.php"><?= $translations['condit'] ?></a>

                </nav>
                <div class="logo-container">
                    <img src="../Resources/uphf.png" alt="Université Polytechnique Logo" width="120" height="60" />
                </div>
            </div>
            <div class="copyright">
                © <span id="current-year"></span> <?= $translations['reserved'] ?>
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('current-year').textContent = new Date().getFullYear();
</script>