<!-- affichage du footer alternants -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-row">
                <div class="logo-container">
                    <img src="/Resources/iut.jpg" alt="Logo" class="logo">
                <nav class="nav-footer">
                    <a href="/View/MentionLegale.php?mention=informations">Informations</a>
                    <a href="/View/MentionLegale.php?mention=a-propos">À propos</a>
                    <a href="/View/MentionLegale.php?mention=confidentialite">Confidentialité</a>
                    <a href="/View/MentionLegale.php?mention=conditions-utilisation">Conditions d'utilisation</a>

                </nav>
                <div class="logo-container">
                    <img src="/Resources/uphf.png" alt="Université Polytechnique Logo" width="120" height="60" />
                </div>
            </div>
            <div class="copyright">
                © <span id="current-year"></span> Tous droits réservés
            </div>
        </div>
    </div>
</footer>

<script>
    document.getElementById('current-year').textContent = new Date().getFullYear();
</script>