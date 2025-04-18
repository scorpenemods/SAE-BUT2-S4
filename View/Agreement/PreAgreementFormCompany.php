<?php
// manage pre agrement send to the company
// On va dabord envoyer la préconvention à l'entreprise

require_once '../../Model/Company.php';
require_once '../../Model/Database.php';
require_once '../../Model/Person.php';

$database = Database::getInstance();

session_start();

//Il faut préremplir les informations

if (isset($_SESSION['personne'])){
    $personne = $_SESSION['personne'];

    $role = $personne->getRole();
    $nom = $personne->getNom();
    $prenom = $personne->getPrenom();
    $telephone = $personne->getTelephone();
    $email = $personne->getEmail();
    $id = $personne->getId();

    if ($role == 3){
        $company = $database->getCompanybyUserId($id);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Le Petit Stage - Pre-convention">
    <title>Le Petit Stage</title>
    <link rel="stylesheet" href="/View/Agreement/PreAgreementForm.css">
</head>
<body>

<div class="form-container">
    <h1>Formulaire de Pré-Convention de Stage</h1>
    <form action="submit_preconvention.php" method="POST">

    <!-- partie entreprise uniquement ici -->
        <h2>Entreprise</h2>

        <!-- Choix du type de stage : France ou à l'étranger -->
        <?php
        if ($company['country'] == "France"){
        ?>

        <div class="form-group">
            <div class="radio-group">
                <!-- Stage en France -->
                <input type="radio" id="france-int" name="internship-type" value="france-int"/>
                <label for="france-int">Stage en France</label>

                <!-- Stage à l'étranger -->
                <input type="radio" id="abroad-int" name="internship-type" value="abroad-int"/>
                <label for="abroad-int">Stage à l'étranger</label>
            </div>
        </div>

        <?php }
        else{
        ?>

            <div class="form-group">
                <div class="radio-group">
                    <!-- Stage en France -->
                    <input type="radio" id="france-int" name="internship-type" value="france-int" disabled/>
                    <label for="france-int">Stage en France</label>

                    <!-- Stage à l'étranger -->
                    <input type="radio" id="abroad-int" name="internship-type" value="abroad-int" checked disabled/>
                    <label for="abroad-int">Stage à l'étranger</label>
                </div>
            </div>


            <div class="form-group" id="country">
                <!-- Champ pour saisir le pays si stage à l'étranger -->
                <label for="country">Pays :</label>
                <input type="text" id="country" name="country" value="<?php echo $company['country']; ?>">
            </div>

        <?php
        }
        ?>


        <!-- Informations générales sur l'entreprise -->
        <div class="form-group">
            <!-- Nom de l'entreprise -->
            <label for="company-name">Nom de l'entreprise</label>
            <input type="text" id="company-name" name="company-name" value="<?php echo $company['name']?>"   required>

            <!-- Adresse de l'entreprise -->
            <label for="company-address">Adresse de l'entreprise</label>
            <input type="text" id="company-address" name="company-address" value="<?php echo $company['address']?>"   required>

            <!-- Code postal et ville de l'entreprise -->
            <label for="company-postal-code">Code Postal</label>
            <input type="text" id="company-postal-code" name="company-postal-code" value="<?php echo $company['postal_code']?>"   required>

            <label for="company-city">Ville</label>
            <input type="text" id="company-city" name="company-city" value="<?php echo $company['city']?>"   required>
        </div>


        <?php
        if ($company['country'] == "France"){
        ?>
        <div class="intership-location" id="intership-location">
            <div class="encadre">
                <!-- Informations obligatoires pour un stage en France uniquement -->
                <h2>À compléter obligatoirement pour établir la convention – pour un stage en France UNIQUEMENT</h2>

                <!-- Section dédiée aux stages en France -->
                <div class="french-internship-only">
                    <!-- Numéro SIRET -->
                    <div class="form-group">
                        <label for="siret">N° SIRET</label>
                        <input type="text" id="siret" name="siret" maxlength="14" minlength="14" required placeholder="14 chiffres" value="<?php echo $company['siret']?>"   />

                        <!-- Informations complémentaires : APE, Effectif, et Statut juridique -->
                        <!-- Code APE -->
                        <label for="ape">Code APE</label>
                        <input type="text" id="ape" name="ape" maxlength="5" placeholder="Ex : 12345" value="<?php echo $company['APE_code']?>"  />

                        <!-- Effectif de l'entreprise -->
                        <label for="workforce">Effectif</label>
                        <input type="text" id="workforce" name="workforce" value="<?php echo $company['size']?>"  />

                        <!-- Statut juridique -->
                        <label for="legal-status">Statut juridique</label>
                        <input type="text" id="legal-status" name="legal-status" placeholder="Ex : SARL, SAS, etc." value="<?php echo $company['legal_status']?>"  />
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>

        <div class="encadre">
            <!-- Section 1 : Représentant Légal -->
            <div class="form-group">
                <h3>1/ Représentant Légal</h3>
                <div class="form-item">
                    <label for="nom_legal">Nom :</label>
                    <input type="text" id="nom_legal" name="nom_legal">
                </div>
            </div>

            <div class="form-group">
                <div class="form-item radio-group">
                    <label>Civilité :</label>
                    <input type="radio" id="mme_legal" name="civilite_legal" value="Mme" >
                    <label for="mme_legal">Mme</label>
                    <input type="radio" id="mr_legal" name="civilite_legal" value="Mr" >
                    <label for="mr_legal">Mr</label>
                </div>
            </div>
            <div class="form-group">
                <div class="form-item">
                    <label for="fonction_legal">Fonction dans l'entreprise :</label>
                    <input type="text" id="fonction_legal" name="fonction_legal" >
                </div>

                <div class="form-item">
                    <label for="mail_legal">Email :</label>
                    <input type="email" id="mail_legal" name="mail_legal" >
                </div>
            </div>

            <!-- Section 2 : Tuteur Entreprise -->
            <div class="form-group">
                <h3>2/ Tuteur Entreprise</h3>
                <div class="form-item">
                    <label for="nom_tuteur">Nom :</label>
                    <input type="text" id="nom_tuteur" name="nom_tuteur" value="<?php echo $nom?>"  >
                </div>
            </div>
            <div class="form-group">
                <div class="form-item radio-group">
                    <label>Civilité :</label>
                    <input type="radio" id="mme_tuteur" name="civilite_tuteur" value="Mme" required>
                    <label for="mme_tuteur">Mme</label>
                    <input type="radio" id="mr_tuteur" name="civilite_tuteur" value="Mr" required>
                    <label for="mr_tuteur">Mr</label>
                </div>
            </div>
            <div class="form-group">
                <div class="form-item">
                    <label for="fonction_tuteur">Fonction dans l'entreprise :</label>
                    <input type="text" id="fonction_tuteur" name="fonction_tuteur" required>
                </div>

                <div class="form-item">
                    <label for="tel_tuteur">Téléphone :</label>
                    <input type="tel" id="tel_tuteur" name="tel_tuteur" pattern="[0-9]{10}" value="<?php echo $telephone?>"   required>
                </div>

                <div class="form-item">
                    <label for="mail_tuteur">Email :</label>
                    <input type="email" id="mail_tuteur" name="mail_tuteur" value="<?php echo $email?>" required>
                </div>

                <div class="form-item">
                    <label for="service_tuteur">Service d'accueil :</label>
                    <input type="text" id="service_tuteur" name="service_tuteur" required>
                </div>

                <div class="form-item">
                    <label for="adresse_tuteur">Adresse (si différente de l’entreprise) :</label>
                    <textarea id="adresse_tuteur" name="adresse_tuteur" rows="3"></textarea>
                </div>
            </div>

            <!-- Section 3 : Signature de la Convention -->
            <div class="form-group">
                <h3>3/ Signature de la Convention</h3>
                <div class="form-item">
                    <label for="nom_signataire">Nom :</label>
                    <input type="text" id="nom_signataire" name="nom_signataire">
                </div>

                <div class="form-item radio-group">
                    <label>Civilité :</label>
                    <input type="radio" id="mme_signataire" name="civilite_signataire" value="Mme" >
                    <label for="mme_signataire">Mme</label>
                    <input type="radio" id="mr_signataire" name="civilite_signataire" value="Mr" >
                    <label for="mr_signataire">Mr</label>
                </div>

                <div class="form-item">
                    <label for="fonction_signataire">Fonction dans l'entreprise :</label>
                    <input type="text" id="fonction_signataire" name="fonction_signataire" >
                </div>

                <div class="form-item">
                    <label for="mail_signataire">Email :</label>
                    <input type="email" id="mail_signataire" name="mail_signataire" >
                </div>
            </div>
        </div>



        <div class="encadre">
            <h2>A compléter obligatoirement</h2>

            <!-- Sujet du stage -->
            <div class="form-group">
                <label for="intership-subject">Sujet du stage (140 caractères max)</label>
                <textarea id="intership-subject" name="intership-subject" rows="3" maxlength="140" placeholder="Décrivez le sujet du stage..." required></textarea>
            </div>

            <!-- Fonctions et Tâches -->
            <div class="form-group">
                <label for="tasks-functions">Fonctions et Tâches (140 caractères max)</label>
                <textarea id="tasks-functions" name="tasks-functions" rows="3" maxlength="140" placeholder="Décrivez les fonctions et tâches..." required></textarea>
            </div>
        </div>

        <!-- Choix du parcours -->
        <div>
            <div class="radio-group">
                <label>Choix du parcours : </label>
                <input type="radio" id="A" name="path-choice" value="A" required>
                <label for="A">Parcours A</label>

                <input type="radio" id="B" name="path-choice" value="B" required>
                <label for="B">Parcours B</label>
            </div>
        </div>

        <!-- Informations complémentaires -->
        <div>
            <p>
                En BUT 2 : 40 % du Temps de Travail en Télétravail maximum par semaine<br>
                En BUT 3 : 60 % du temps de Travail en Télétravail maximum par semaine
            </p>
        </div>

        <div class="encadre">
            <h2>A compléter obligatoirement</h2>

            <!-- Confidentialité du sujet du stage -->
            <div class="form-group">
                <div class="radio-group">
                    <label>Confidentialité du sujet du stage :</label>
                    <input type="radio" id="yes" name="confidentiality" value="yes" required>
                    <label for="yes">Oui</label>
                    <input type="radio" id="no" name="confidentiality" value="no" required>
                    <label for="no">Non</label>
                </div>
            </div>

            <!-- Dates du stage -->
            <div class="form-group">
                <label for="intership-dates-beginning">Date de début du stage</label>
                <input type="date" id="intership-dates-beginning" name="intership-dates" required>
                <label for="intership-dates-ending">Date de fin du stage</label>
                <input type="date" id="intership-dates-ending" name="intership-dates">
            </div>

            <!-- Interruption au cours du stage -->
            <div class="form-group">
                <div class="radio-group">
                    <label>Interruption au cours du stage :</label>
                    <input type="radio" id="yes-interruption" name="interruption" value="yes" required>
                    <label for="yes-interruption">Oui</label>
                    <input type="radio" id="no-interruption" name="interruption" value="no" required>
                    <label for="no-interruption">Non</label>
                </div>
            </div>

            <!-- Dates de l'interruption -->
            <div class="form-group">
                <label for="interruption-dates">Si oui, dates prévues :</label>
                <input type="text" id="interruption-dates" name="interruption-dates" placeholder="du jj/mm/aaaa au jj/mm/aaaa">
            </div>

            <!-- Durée effective du stage -->
            <div class="form-group">
                <label for="intership-duration">Durée effective du stage en heures :</label>
                <input type="text" id="intership-duration" name="intership-duration" required>
            </div>

            <!-- Horaires hebdomadaires et type de planning -->
            <div class="form-group">
                <label for="schedules">Horaires hebdomadaires (ex : 35.00) :</label>
                <input type="text" id="schedules" name="schedules" required>

                <div class="radio-group">
                    <input type="radio" id="full-time" name="schedules-type" value="full-time" required>
                    <label for="full-time">Temps plein</label>
                    <input type="radio" id="part-time" name="schedules-type" value="part-time" required>
                    <label for="part-time">Temps partiel</label>
                </div>
            </div>

            <!-- Gratification du stage -->
            <div class="form-group">
                <p>Gratification du stage :</p>
                <label for="gratification">Montant de la gratification (cf. décret) :</label>
                <input type="text" id="gratification" name="gratification" required>

                <div class="radio-group">
                    <input type="radio" id="month" name="month-hour" value="month" required>
                    <label for="month">par mois</label>
                    <input type="radio" id="hour" name="month-hour" value="hour" required>
                    <label for="hour">par heure</label>
                </div>

                <div class="radio-group">
                    <input type="radio" id="gross" name="salary" value="gross" required>
                    <label for="gross">en brut</label>
                    <input type="radio" id="net" name="salary" value="net" required>
                    <label for="net">en net</label>
                </div>
            </div>

            <!-- Mode de versement -->
            <div class="form-group">
                <label for="payment-method">Mode de versement :</label>
                <input type="text" id="payment-method" name="payment-method" required>

                <div class="radio-group">
                    <input type="radio" id="checks" name="payment-type" value="checks" required>
                    <label for="checks">Chèque</label>
                    <input type="radio" id="transfer" name="payment-type" value="transfer" required>
                    <label for="transfer">Virement</label>
                    <input type="radio" id="cash" name="payment-type" value="cash" required>
                    <label for="cash">Espèces</label>
                </div>
            </div>
        </div>


        <!-- Dates et Signatures à mettre quand toutes les parties auront remplis la conv
        <div class="form-group">
            <h2>Signatures et Validation</h2>

            <label for="date_signature_tuteur">Date (Signature Tuteur Entreprise) :</label>
            <input type="date" id="date_signature_tuteur" name="date_signature_tuteur" required>
        </div>

         Canvas pour dessiner la signature
        <div class="signature">
            <label for="signature">Signature :</label>
            <canvas id="signatureCanvas" width="400" height="100" style="border: 1px solid #000;"></canvas>
            <br>
            <button type="button" onclick="clearSignature()">Effacer</button>
            <br>
             Champ caché pour envoyer la signature en base64
            <input type="hidden" id="signatureData" name="signatureData">
        </div>
        -->

        <button type="submit">Soumettre aux autres parties</button>
    </form>
</div>


</body>
</html>



