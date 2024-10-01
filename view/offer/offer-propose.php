<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'offre - Le Petit Stage</title>

    <link rel="stylesheet" href="/view/css/detail.css">
    <link rel="stylesheet" href="/view/css/offer-propose.css">
    <link rel="stylesheet" href="/view/css/header.css">
    <link rel="stylesheet" href="/view/css/footer.css">
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include dirname(__FILE__) . '/../header.php'; ?>
<main>
    <h2>Proposer une offre</h2>
    <form>
        <div class="form-container">
            <div class="form-group">
                <label for="titre">Titre de l'offre :</label>
                <input type="text" id="titre" name="titre" placeholder="Titre de l'offre">
            </div>
            <div class="form-group">
                <label for="entreprise">Entreprise :</label>
                <input type="text" id="entreprise" name="entreprise" placeholder="Nom de l'entreprise">
            </div>
        </div>
        <div class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="duree">Durée :</label>
                    <input type="text" id="duree" name="duree" placeholder="Durée">
                </div>
                <div class="form-group">
                    <label for="lieu">Lieu :</label>
                    <input type="text" id="lieu" name="lieu" placeholder="Lieu">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="date-debut">Date de début :</label>
                    <input type="date" id="date-debut" name="date-debut">
                </div>
                <div class="form-group">
                    <label for="date-fin">Date de fin :</label>
                    <input type="date" id="date-fin" name="date-fin">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="diplome">Diplôme requis :</label>
                    <input type="text" id="diplome" name="diplome" placeholder="Diplôme requis">
                </div>
                <div class="form-group">
                    <label for="remuneration">Rémunération :</label>
                    <input type="text" id="remuneration" name="remuneration" placeholder="Rémunération">
                </div>
            </div>
        </div>
        <div class="form-container">
            <div class="form-group">
                <label for="description">Description de l'offre :</label>
                <textarea id="description" name="description" placeholder="Description de l'offre"></textarea>
            </div>
        </div>
        <div class="form-container">
            <h3>Contact de l'entreprise :</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">
                        <span style="display: inline-block; width: 16px; height: 16px; background-color: #ccc; margin-right: 5px; vertical-align: middle;"></span>
                        Téléphone :
                    </label>
                    <input type="text" id="phone" name="phone" placeholder="Téléphone">
                </div>
                <div class="form-group">
                    <label for="email">
                        <span style="display: inline-block; width: 16px; height: 16px; background-color: #ccc; margin-right: 5px; vertical-align: middle;"></span>
                        Email :
                    </label>
                    <input type="text" id="email" name="email" placeholder="Email">
                </div>
            </div>
        </div>
    </form>
</main>
<?php include dirname(__FILE__) . '/../footer.php'; ?>
</body>