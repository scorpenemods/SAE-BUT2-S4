<footer>

    <div class="container">
        <div class="footer-content">
            <div class="footer-row">
                <div class="logo-container">
                    <img src="/Ressources/iut.jpg" alt="IUT Logo" width="120" height="60" />
                </div>

                <nav class="nav-footer">
                    <a href="/informations">Informations</a>
                    <a href="/a-propos">À propos</a>
                    <a href="/confidentialite">Confidentialité</a>
                    <a href="/conditions-utilisation">Conditions d'utilisation</a>
                </nav>
                <div class="logo-container">
                    <img src="../Ressources/uphf.png" alt="Université Polytechnique Logo" width="120" height="60" />
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