<!DOCTYPE html>
<html>
<body>
<h1>Redirection...</h1>
<p>Retour à la page d'accueil dans 3 secondes...</p>

<script>
    // JavaScript redirige vers l'index après 3 seconds
    setTimeout(function() {
        window.location.href = "Index/index.php";
    }, 3000);
</script>

</body>
</html>