<?php

require_once '../Model/Company.php';
require_once '../Model/Database.php';
require_once '../Model/Person.php';

$database = Database::getInstance();

session_start();

if (isset($_SESSION['personne'])){

    $readonly = "";
    $checked = "";

    //recuperer les infos de la préconvention si on la consulte après l'avoir créer précedemment
    if (isset($_GET['id'])){ //si y'a déjà des valeurs rentrées
        $idPreConv = $_GET['id'];
        $liste = $database->getInputsPreAgreementForm($idPreConv);
        $inputs = json_decode($liste['inputs'], true);

        $status = $database->PreAgreementIsValid($idPreConv); //recupere le status de la preconvention

        if ($status == 1){
            $readonly = "readonly";
            $checked = "checked disabled";
        }
        else{
            $readonly = "";
            $checked = "";
        }
    }



    $personne = $_SESSION['personne'];

    $role = $personne->getRole();
    $nom = $personne->getNom();
    $activite = $personne->getActivite();
    $prenom = $personne->getPrenom();
    $telephone = $personne->getTelephone();
    $email = $personne->getEmail();
    $id = $personne->getId();

    if ($role == 1){ //si c'est l'élève
        if (isset($_GET['tutor'])) { // on récup le tuteur séléctionné
            $tutor = htmlspecialchars($_GET['tutor']);
        } else{
            header('Location: index.php'); //on renvoie à index car pas possible qu'il n'y ai pas eu de séléction
            exit();
        }
    }


}else{
    header('Location: index.php');
    exit();
}


function getFieldValue($field, $inputs = null, $default = null) {
    if (isset($inputs[$field])) {
        return htmlspecialchars($inputs[$field]); // Priorité aux valeurs de $inputs
    }
    if ($default !== null) {
        return htmlspecialchars($default); // Sinon, utilise la valeur par défaut
    }
    return ''; // Sinon, champ vide
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
    <form action="SubmitPreAgreementStudent.php" method="POST">

        <!-- Section Étudiant -->
        <section class="form-section">
            <h2>Étudiant</h2>
            <div class="form-group">
                <label for="nom-student">Nom</label>
                <input type="text" id="nom-student" name="nom-student" value="<?php echo getFieldValue('nomStudent', $inputs, $nom); ?>" <?php echo $readonly ?> required>
                <label for="prenom-student">Prénom</label>
                <input type="text" id="prenom-student" name="prenom-student" value="<?php echo getFieldValue('prenomStudent', $inputs, $prenom); ?>" <?php echo $readonly ?> required>

                <label for="num-student">Numéro Étudiant</label>
                <input type="text" id="num-student" name="num-student" value="<?php echo getFieldValue('numStudent', $inputs); ?>" <?php echo $readonly ?> required>

                <div class="radio-group">
                    <label>Niveau de Formation :</label>
                    <input type="radio" id="but2" name="formation-level" value="BUT2" <?php echo (getFieldValue('formationLevel', $inputs) === 'BUT2') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="but2">BUT 2</label>
                    <input type="radio" id="but3" name="formation-level" value="BUT3" <?php echo (getFieldValue('formationLevel', $inputs) === 'BUT3') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="but3">BUT 3</label>
                </div>

                <label for="formation">Intitulé de la formation</label>
                <input type="text" id="formation" name="formation" value="<?php echo getFieldValue('formation', $inputs, $activite); ?>" <?php echo $readonly ?> required>
            </div>

            <!-- Type de stage -->
            <div class="form-group">
                <div class="radio-group">
                    <label>Obligation du stage :</label>
                    <input type="radio" id="obligatory" name="obligation" value="obligatory" <?php echo (getFieldValue('obligation', $inputs) === 'obligatory') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="obligatory">Stage Obligatoire</label>
                    <input type="radio" id="not-obligatory" name="obligation" value="not-obligatory" <?php echo (getFieldValue('obligation', $inputs) === 'not-obligatory') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="not-obligatory">Non obligatoire</label>
                </div>
            </div>

            <div class="form-group">
                <div class="radio-group">
                    <label>Type de candidature :</label>
                    <input type="radio" id="spontaneous-application" name="application-type" value="spontaneous-application" <?php echo (getFieldValue('applicationType', $inputs) === 'spontaneous-application') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="spontaneous-application">Candidature Spontanée</label>
                    <input type="radio" id="response" name="application-type" value="response" <?php echo (getFieldValue('applicationType', $inputs) === 'response') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="response">Réponse à une offre</label>
                    <input type="radio" id="network" name="application-type" value="network" <?php echo (getFieldValue('applicationType', $inputs) === 'network') ? 'checked' : ''; ?> <?php echo $checked ?> required>
                    <label for="network">Réseau de connaissance</label>
                </div>
            </div>

            <div class="form-group">
                <label for="adress-student">Adresse durant le stage</label>
                <input type="text" id="adress-student" name="adress-student" value="<?php echo getFieldValue('address', $inputs); ?>" <?php echo $readonly ?> required>

                <label for="postal-code-student">Code Postal</label>
                <input type="text" id="postal-code-student" name="postal-code-student" value="<?php echo getFieldValue('postalCodeStudent', $inputs); ?>" <?php echo $readonly ?> required>

                <label for="student-city">Ville</label>
                <input type="text" id="student-city" name="student-city" value="<?php echo getFieldValue('cityStudent', $inputs); ?>" <?php echo $readonly ?> required>

                <label for="phone-student">Téléphone (Fixe)</label>
                <input type="text" id="phone-student" name="phone-student" value="<?php echo getFieldValue('postalCodeStudent', $inputs);  ?> " <?php echo $readonly ?>>

                <label for="phone-number-student">Téléphone (Portable)</label>
                <input type="text" id="phone-number-student" name="phone-number-student" value="<?php echo getFieldValue('phoneNumberStudent', $inputs, $telephone); ?>" <?php echo $readonly ?> required>

                <label for="email-student">Email</label>
                <input type="email" id="email-student" name="email-student" value="<?php echo getFieldValue('emailStudent', $inputs, $email); ?>" <?php echo $readonly ?> required>

                <input type="hidden" name="mentor" value="<?php echo $tutor ?>" />
                <input type="hidden" name="student-id" value="<?php echo $id ?>" />
                <input type="hidden" name="id-pre-conv" value="<?php echo $idPreConv ?? -1 ?>"

            </div>
        </section>

        <h2>Entreprise</h2>

        <!-- Choix du type de stage : France ou à l'étranger -->
        <div class="form-group">
            <div class="radio-group">
                <!-- Stage en France -->
                <input type="radio" id="france-int" name="internship-type" value="france-int" <?php echo (getFieldValue('intershipType', $inputs) === 'france-int') ? 'checked' : ''; ?>  onclick="toggleInputFr()"/>
                <label for="france-int">Stage en France</label>

                <!-- Stage à l'étranger -->
                <input type="radio" id="abroad-int" name="internship-type" value="abroad-int" <?php echo (getFieldValue('intershipType', $inputs) === 'abroad-int') ? 'checked' : ''; ?>  onclick="toggleInputAb()"/>
                <label for="abroad-int">Stage à l'étranger</label>
            </div>
        </div>

        <div class="form-group" id="country">
            <!-- Champ pour saisir le pays si stage à l'étranger -->
            <label for="country">Pays :</label>
            <input type="text" id="country" name="country" value="<?php echo getFieldValue('country', $inputs); ?>">
        </div>

        <!-- Informations générales sur l'entreprise -->
        <div class="form-group">
            <!-- Nom de l'entreprise -->
            <label for="company-name">Nom de l'entreprise</label>
            <input type="text" id="company-name" name="company-name" value="<?php echo getFieldValue('companyName', $inputs); ?>">

            <!-- Adresse de l'entreprise -->
            <label for="company-address">Adresse de l'entreprise</label>
            <input type="text" id="company-address" name="company-address" value="<?php echo getFieldValue('companyAddress', $inputs); ?>">

            <!-- Code postal et ville de l'entreprise -->
            <label for="company-postal-code">Code Postal</label>
            <input type="text" id="company-postal-code" name="company-postal-code"  value="<?php echo getFieldValue('companyPostalCode', $inputs); ?>">

            <label for="company-city">Ville</label>
            <input type="text" id="company-city" name="company-city" value="<?php echo getFieldValue('companyCity', $inputs); ?>">
        </div>


        <div class="intership-location" id="intership-location">
            <div class="encadre">
                <!-- Informations obligatoires pour un stage en France uniquement -->
                <h2>À compléter obligatoirement pour établir la convention – pour un stage en France UNIQUEMENT</h2>

                <!-- Section dédiée aux stages en France -->
                <div class="french-internship-only">
                    <!-- Numéro SIRET -->
                    <div class="form-group">
                        <label for="siret">N° SIRET</label>
                        <input type="text" id="siret" name="siret" maxlength="14" minlength="14"  placeholder="14 chiffres"  value="<?php echo getFieldValue('siret', $inputs); ?>"/>

                        <!-- Informations complémentaires : APE, Effectif, et Statut juridique -->
                        <!-- Code APE -->
                        <label for="ape">Code APE</label>
                        <input type="text" id="ape" name="ape" maxlength="5" placeholder="Ex : 12345"  value="<?php echo getFieldValue('ape', $inputs); ?>"/>

                        <!-- Effectif de l'entreprise -->
                        <label for="workforce">Effectif</label>
                        <input type="text" id="workforce" name="workforce" value="<?php echo getFieldValue('workforce', $inputs); ?>"/>

                        <!-- Statut juridique -->
                        <label for="legal-status">Statut juridique</label>
                        <input type="text" id="legal-status" name="legal-status" placeholder="Ex : SARL, SAS, etc." value="<?php echo getFieldValue('legalStatus', $inputs); ?>" />
                    </div>
                </div>
            </div>
        </div>

        <div class="encadre">
            <!-- Section 1 : Représentant Légal -->
            <div class="form-group">
                <h3>1/ Représentant Légal</h3>
                <div class="form-item">
                    <label for="nom_legal">Nom :</label>
                    <input type="text" id="nom_legal" name="nom_legal"  value="<?php echo getFieldValue('nomLegal', $inputs); ?>" >
                </div>
            </div>

            <div class="form-group">
                <div class="form-item radio-group">
                    <label>Civilité :</label>
                    <input type="radio" id="mme_legal" name="civilite_legal" value="Mme" <?php echo (getFieldValue('civiliteLegal', $inputs) === 'Mme') ? 'checked' : ''; ?> >
                    <label for="mme_legal">Mme</label>
                    <input type="radio" id="mr_legal" name="civilite_legal" value="Mr" <?php echo (getFieldValue('civiliteLegal', $inputs) === 'Mr') ? 'checked' : ''; ?>>
                    <label for="mr_legal">Mr</label>
                </div>
            </div>
            <div class="form-group">
                <div class="form-item">
                    <label for="fonction_legal">Fonction dans l'entreprise :</label>
                    <input type="text" id="fonction_legal" name="fonction_legal" value="<?php echo getFieldValue('fonctionLegal', $inputs); ?>" >
                </div>

                <div class="form-item">
                    <label for="mail_legal">Email :</label>
                    <input type="email" id="mail_legal" name="mail_legal" value="<?php echo getFieldValue('mailLegal', $inputs); ?>" >
                </div>
            </div>

            <!-- Section 2 : Tuteur Entreprise -->
            <div class="form-group">
                <h3>2/ Tuteur Entreprise</h3>
                <div class="form-item">
                    <label for="nom_tuteur">Nom :</label>
                    <input type="text" id="nom_tuteur" name="nom_tuteur" value="<?php echo getFieldValue('nomTuteur', $inputs); ?>" >
                </div>
            </div>
            <div class="form-group">
                <div class="form-item radio-group">
                    <label>Civilité :</label>
                    <input type="radio" id="mme_tuteur" name="civilite_tuteur" value="Mme" <?php echo (getFieldValue('civiliteTuteur', $inputs) === 'Mme') ? 'checked' : ''; ?>>
                    <label for="mme_tuteur">Mme</label>
                    <input type="radio" id="mr_tuteur" name="civilite_tuteur" value="Mr" <?php echo (getFieldValue('civiliteTuteur', $inputs) === 'Mr') ? 'checked' : ''; ?>>
                    <label for="mr_tuteur">Mr</label>
                </div>
            </div>
            <div class="form-group">
                <div class="form-item">
                    <label for="fonction_tuteur">Fonction dans l'entreprise :</label>
                    <input type="text" id="fonction_tuteur" name="fonction_tuteur" value="<?php echo getFieldValue('fonctionTuteur', $inputs); ?>" >
                </div>

                <div class="form-item">
                    <label for="tel_tuteur">Téléphone :</label>
                    <input type="tel" id="tel_tuteur" name="tel_tuteur" pattern="[0-9]{10}" value="<?php echo getFieldValue('telTuteur', $inputs); ?>" >
                </div>

                <div class="form-item">
                    <label for="mail_tuteur">Email :</label>
                    <input type="email" id="mail_tuteur" name="mail_tuteur" value="<?php echo getFieldValue('mailTuteur', $inputs); ?>">
                </div>

                <div class="form-item">
                    <label for="service_tuteur">Service d'accueil :</label>
                    <input type="text" id="service_tuteur" name="service_tuteur" value="<?php echo getFieldValue('serviceTuteur', $inputs); ?>" >
                </div>

                <div class="form-item">
                    <label for="adresse_tuteur">Adresse (si différente de l’entreprise) :</label>
                    <input type="text" id="adresse_tuteur" name="adresse_tuteur" value="<?php echo getFieldValue('adresseTuteur', $inputs); ?>">
                </div>
            </div>

            <!-- Section 3 : Signature de la Convention -->
            <div class="form-group">
                <h3>3/ Signature de la Convention</h3>
                <div class="form-item">
                    <label for="nom_signataire">Nom :</label>
                    <input type="text" id="nom_signataire" name="nom_signataire" value="<?php echo getFieldValue('nomSignataire', $inputs); ?>" >
                </div>

                <div class="form-item radio-group">
                    <label>Civilité :</label>
                    <input type="radio" id="mme_signataire" name="civilite_signataire" value="Mme" <?php echo (getFieldValue('civiliteSignataire', $inputs) === 'Mme') ? 'checked' : ''; ?>>
                    <label for="mme_signataire">Mme</label>
                    <input type="radio" id="mr_signataire" name="civilite_signataire" value="Mr" <?php echo (getFieldValue('civiliteSignataire', $inputs) === 'Mr') ? 'checked' : ''; ?>>
                    <label for="mr_signataire">Mr</label>
                </div>

                <div class="form-item">
                    <label for="fonction_signataire">Fonction dans l'entreprise :</label>
                    <input type="text" id="fonction_signataire" name="fonction_signataire" value="<?php echo getFieldValue('fonctionSignataire', $inputs); ?>" >
                </div>

                <div class="form-item">
                    <label for="mail_signataire">Email :</label>
                    <input type="email" id="mail_signataire" name="mail_signataire" value="<?php echo getFieldValue('mailSignataire', $inputs); ?>" >
                </div>
            </div>
        </div>



        <div class="encadre">
            <h2>A compléter obligatoirement</h2>

            <!-- Sujet du stage -->
            <div class="form-group">
                <label for="intership-subject">Sujet du stage (140 caractères max)</label>
                <textarea id="intership-subject" name="intership-subject" rows="3" maxlength="140" placeholder="Décrivez le sujet du stage..."  > <?php echo htmlspecialchars(getFieldValue('intershipSubject', $inputs, '')); ?> </textarea>
            </div>

            <!-- Fonctions et Tâches -->
            <div class="form-group">
                <label for="tasks-functions">Fonctions et Tâches (140 caractères max)</label>
                <textarea id="tasks-functions" name="tasks-functions" rows="3" maxlength="140" placeholder="Décrivez les fonctions et tâches..."  > <?php echo htmlspecialchars(getFieldValue('tasksFunctions', $inputs, '')); ?> </textarea>
            </div>
        </div>

        <!-- Choix du parcours -->
        <div>
            <div class="radio-group">
                <label>Choix du parcours : </label>
                <input type="radio" id="A" name="path-choice" value="A" <?php echo (getFieldValue('pathChoice', $inputs) === 'A') ? 'checked' : ''; ?>>
                <label for="A">Parcours A</label>

                <input type="radio" id="B" name="path-choice" value="B" <?php echo (getFieldValue('pathChoice', $inputs) === 'B') ? 'checked' : ''; ?>>
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
                    <input type="radio" id="yes" name="confidentiality" value="yes" <?php echo (getFieldValue('confidentiality', $inputs) === 'yes') ? 'checked' : ''; ?>>
                    <label for="yes">Oui</label>
                    <input type="radio" id="no" name="confidentiality" value="no" <?php echo (getFieldValue('confidentiality', $inputs) === 'no') ? 'checked' : ''; ?>>
                    <label for="no">Non</label>
                </div>
            </div>

            <!-- Dates du stage -->
            <div class="form-group">
                <label for="intership-dates-beginning">Date de début du stage</label>
                <input type="date" id="intership-dates-beginning" name="intership-dates-beginning" value="<?php echo getFieldValue('intershipDatesBeginning', $inputs); ?>" >

                <label for="intership-dates-ending">Date de fin du stage</label>
                <input type="date" id="intership-dates-ending" name="intership-dates-ending" value="<?php echo getFieldValue('intershipDatesEnding', $inputs); ?>" >
            </div>

            <!-- Interruption au cours du stage -->
            <div class="form-group">
                <div class="radio-group">
                    <label>Interruption au cours du stage :</label>
                    <input type="radio" id="yes-interruption" name="interruption" value="yes" <?php echo (getFieldValue('interruption', $inputs) === 'yes') ? 'checked' : ''; ?>>
                    <label for="yes-interruption">Oui</label>
                    <input type="radio" id="no-interruption" name="interruption" value="no" <?php echo (getFieldValue('interruption', $inputs) === 'no') ? 'checked' : ''; ?>>
                    <label for="no-interruption">Non</label>
                </div>
            </div>

            <!-- Dates de l'interruption -->
            <div class="form-group">
                <label for="interruption-dates">Si oui, dates prévues :</label>
                <input type="text" id="interruption-dates" name="interruption-dates" placeholder="du jj/mm/aaaa au jj/mm/aaaa" value="<?php echo getFieldValue('interruptionDates', $inputs); ?>"  >
            </div>

            <!-- Durée effective du stage -->
            <div class="form-group">
                <label for="intership-duration">Durée effective du stage en heures :</label>
                <input type="text" id="intership-duration" name="intership-duration"  value="<?php echo getFieldValue('intershipDuration', $inputs); ?>">
            </div>

            <!-- Horaires hebdomadaires et type de planning -->
            <div class="form-group">
                <label for="schedules">Horaires hebdomadaires (ex : 35.00) :</label>
                <input type="text" id="schedules" name="schedules" value="<?php echo getFieldValue('schedules', $inputs); ?>" >

                <div class="radio-group">
                    <input type="radio" id="full-time" name="schedules-type" value="full-time" <?php echo (getFieldValue('schedulesType', $inputs) === 'full-time') ? 'checked' : ''; ?> >
                    <label for="full-time">Temps plein</label>
                    <input type="radio" id="part-time" name="schedules-type" value="part-time" <?php echo (getFieldValue('schedulesType', $inputs) === 'part-time') ? 'checked' : ''; ?>>
                    <label for="part-time">Temps partiel</label>
                </div>
            </div>

            <!-- Gratification du stage -->
            <div class="form-group">
                <p>Gratification du stage :</p>
                <label for="gratification">Montant de la gratification (cf. décret) :</label>
                <input type="text" id="gratification" name="gratification" value="<?php echo getFieldValue('gratification', $inputs); ?>" >

                <div class="radio-group">
                    <input type="radio" id="month" name="month-hour" value="month" <?php echo (getFieldValue('monthHour', $inputs) === 'month') ? 'checked' : ''; ?>>
                    <label for="month">par mois</label>
                    <input type="radio" id="hour" name="month-hour" value="hour" <?php echo (getFieldValue('monthHour', $inputs) === 'hour') ? 'checked' : ''; ?>>
                    <label for="hour">par heure</label>
                </div>

                <div class="radio-group">
                    <input type="radio" id="gross" name="salary" value="gross" <?php echo (getFieldValue('salaryType', $inputs) === 'gross') ? 'checked' : ''; ?>>
                    <label for="gross">en brut</label>
                    <input type="radio" id="net" name="salary" value="net" <?php echo (getFieldValue('salaryType', $inputs) === 'net') ? 'checked' : ''; ?>>
                    <label for="net">en net</label>
                </div>
            </div>

            <!-- Mode de versement -->
            <div class="form-group">
                <label for="payment-type">Mode de versement :</label>
                <div class="radio-group">
                    <input type="radio" id="checks" name="payment-type" value="checks" <?php echo (getFieldValue('paymentType', $inputs) === 'chekcs') ? 'checked' : ''; ?>>
                    <label for="checks">Chèque</label>
                    <input type="radio" id="transfer" name="payment-type" value="transfer" <?php echo (getFieldValue('paymentType', $inputs) === 'transfer') ? 'checked' : ''; ?>>
                    <label for="transfer">Virement</label>
                    <input type="radio" id="cash" name="payment-type" value="cash" <?php echo (getFieldValue('paymentType', $inputs) === 'cash') ? 'checked' : ''; ?>>
                    <label for="cash">Espèces</label>
                </div>
            </div>
        </div>


        <!-- Dates et Signatures -->
        <div class="form-group">
            <h2>Validation de la pré-convention</h2>

            <label for="date_signature_etudiant">Date de validation de l'étudiant :</label>
            <div id="signature-pad"></div>
            <input type="date" id="date_signature_etudiant" name="date_signature_etudiant" value="<?php echo getFieldValue('dateSignatureEtudiant', $inputs); ?>" >

            <label for="date_signature_tuteur">Date de validation du tuteur entreprise :</label>
            <input type="date" id="date_signature_tuteur" name="date_signature_tuteur" value="<?php echo getFieldValue('dateSignatureTuteur', $inputs); ?>" >
        </div>

        <div class="form-group">
            <div class="radio-group">
                <label>Validation par le responsable de l’UE Professionnalisation :</label>
                <input type="radio" id="validation_oui" name="validation" value="yes" <?php echo (getFieldValue('validation', $inputs) === 'yes') ? 'checked' : ''; ?>>
                <label for="validation_oui">Oui</label>
                <input type="radio" id="validation_non" name="validation" value="no" <?php echo (getFieldValue('validation', $inputs) === 'yes') ? 'checked' : ''; ?>>
                <label for="validation_non">Non</label>
            </div>
        </div>

        <div class="form-group">
            <label for="responsable_nom">Nom du responsable :</label>
            <input type="text" id="responsable_nom" name="responsable_nom" value="<?php echo getFieldValue('responsableNom', $inputs); ?>" >

            <label for="responsable_prenom">Prénom du responsable :</label>
            <input type="text" id="responsable_prenom" name="responsable_prenom" value="<?php echo getFieldValue('responsablePrenom', $inputs); ?>" >
        </div>

        <div class="form-group">
            <label for="enseignant_referent">Enseignant référent :</label>
            <input type="text" id="enseignent_referent" name="enseignent_referent" value="<?php echo getFieldValue('enseignantReferent', $inputs); ?>" >
        </div>

        <?php if ($role==1){ ?>
            <button type="submit" name="action" value="action1">Enregistrer et soumettre aux autres parties</button>
        <?php } ?>

        <?php if ($role==4 || $role==5){ ?>
            <button type="submit" name="action" value="action2">Enregistrer et soumettre aux autres parties</button>
            <button type="submit" name="action" value="action3">Valider définitivement ce formulaire de pré-convention</button>
        <?php } ?>


    </form>
</div>


<script>
    function toggleInputFrDiv(){
        if (inputContainer.style.display === 'none'){
            inputContainer.style.display = 'block';
        }
        else{
            inputContainer.style.display = 'block';
        }
    }
    function toggleInputAb() {
        const inputContainer1 = document.getElementById('intership-location');
        inputContainer1.style.display = 'none';
        const inputContainer2 = document.getElementById('country');
        inputContainer2.style.display = 'block';
    }
    function toggleInputFr(){
        const inputContainer1 = document.getElementById('intership-location');
        inputContainer1.style.display = 'block';
        const inputContainer2 = document.getElementById('country');
        inputContainer2.style.display = 'none';
        const country = document.getElementById('country');
    }
</script>

</body>
</html>



