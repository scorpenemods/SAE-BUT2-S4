<?php
include "../Model/Email.php";
session_start();

// Validation des données de session
if (!isset($_SESSION['user_name']) || !isset($_SESSION['person'])) {
    die("Session invalide. Veuillez vous reconnecter.");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Rupture de Stage</title>
    <script>
        // Envoi de la demande via une requête asynchrone (AJAX)
        async function EnvoyerDemande() {
            const raison = document.getElementById('raison').value.trim();
            if (!raison) {
                alert("Veuillez fournir une raison pour la demande.");
                return;
            }

            try {
                const response = await fetch("envoyer_demande.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ raison }),
                });

                const result = await response.json();
                if (response.ok) {
                    alert(result.message || "Demande envoyée avec succès !");
                } else {
                    alert(result.error || "Une erreur s'est produite.");
                }
            } catch (error) {
                console.error("Erreur lors de l'envoi de la demande :", error);
                alert("Erreur réseau, veuillez réessayer.");
            }
        }
    </script>
</head>
<body>
<h1>Demander la Rupture de Stage</h1>
<form onsubmit="event.preventDefault(); EnvoyerDemande();">
    <label for="raison">Raison de la demande :</label>
    <input type="text" id="raison" required>
    <button type="submit">Envoyer la demande</button>
</form>
</body>
</html>
