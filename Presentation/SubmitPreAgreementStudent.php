<?php
// manage of pre agreement submit for student
require_once '../Model/Database.php';
$database = Database::getInstance();

$role = $_POST['role'];


if ($_POST['action'] == 'action5'){
    if ($role == 1){
        header('location: Student.php');
        exit();
    }
    else if ($role == 2){
        header('location: Professor.php');
        exit();
    }
    else if ($role == 3){
        header('location: MaitreStage.php');
        exit();
    }
    else if ($role == 4 || $role == 5){
        header('location: Secretariat.php');
        exit();
    }
}


//partie étudiant
$nomStudent =  $_POST['nom-student'];
$prenomStudent =  $_POST['prenom-student'];
$numStudent =  $_POST['num-student'];
$formationLevel =  $_POST['formation-level'];
$formation =  $_POST['formation'];
$obligation  =  $_POST['obligation'];
$applicationType =  $_POST['application-type'];
$address =  $_POST['adress-student'];
$postalCodeStudent =  $_POST['postal-code-student'];
$cityStudent =  $_POST['student-city'];
$phoneStudent =  $_POST['phone-student'] ?? null;
$phoneNumberStudent =  $_POST['phone-number-student'];
$emailStudent =  $_POST['email-student'];
$idStudent = $_POST['student-id'];
$idPreConv = $_POST['id-pre-conv'];

//partie entreprise
$internshipType = $_POST['internship-type'] ?? null;
$country = $_POST['country'] ?? null;
$companyName = $_POST['company-name'] ?? null;
$companyAddress = $_POST['company-address'] ?? null;
$companyPostalCode = $_POST['company-postal-code'] ?? null;
$companyCity = $_POST['company-city'] ?? null;

$siret = $_POST['siret'] ?? null;
$ape = $_POST['ape'] ?? null;
$workforce = $_POST['workforce'] ?? null;
$legalStatus = $_POST['legal-status'] ?? null;

$nomLegal = $_POST['nom_legal'] ?? null;
$civiliteLegal = $_POST['civilite_legal'] ?? null;
$fonctionLegal = $_POST['fonction_legal'] ?? null;
$mailLegal = $_POST['mail_legal'] ?? null;

$nomTuteur = $_POST['nom_tuteur'] ?? null;

if ($nomTuteur !== ""){
    $database->setMentorPreAgreement($idPreConv, $nomTuteur);
}

$civiliteTuteur = $_POST['civilite_tuteur'] ?? null;
$fonctionTuteur = $_POST['fonction_tuteur'] ?? null;
$telTuteur = $_POST['tel_tuteur'] ?? null;
$mailTuteur = $_POST['mail_tuteur'] ?? null;
$serviceTuteur = $_POST['service_tuteur'] ?? null;
$adresseTuteur = $_POST['adresse_tuteur'] ?? null;

$nomSignataire = $_POST['nom_signataire'] ?? null;
$civiliteSignataire = $_POST['civilite_signataire'] ?? null;
$fonctionSignataire = $_POST['fonction_signataire'] ?? null;
$mailSignataire = $_POST['mail_signataire'] ?? null;

$intershipSubject = $_POST['intership-subject'] ?? null;
$tasksFunctions = $_POST['tasks-functions'] ?? null;
$pathChoice = $_POST['path-choice']  ?? null;
$confidentiality = $_POST['confidentiality']  ?? null;
$intershipDatesBeginning = $_POST['intership-dates-beginning']  ?? null;
$intershipDatesEnding = $_POST['intership-dates-ending']  ?? null;
$interruption = $_POST['interruption']  ?? null;
$interruptionDates = $_POST['interruption-dates']  ?? null;
$intershipDuration = $_POST['intership-duration']  ?? null;
$schedules = $_POST['schedules']  ?? null;
$schedulesType = $_POST['schedules-type']  ?? null;

// Gratification
$gratification = $_POST['gratification']  ?? null;
$monthHour = $_POST['month-hour']  ?? null;
$salaryType = $_POST['salary']  ?? null;
$paymentType = $_POST['payment-type']  ?? null;

// Partie Dates et Signatures
$dateSignatureEtudiant = $_POST['date_signature_etudiant'] ?? null;
$dateSignatureTuteur = $_POST['date_signature_tuteur'] ?? null;

//responsable de l’UE Professionnalisation
$validation = $_POST['validation'] ?? null;

$responsableNom = $_POST['responsable_nom'] ?? null;
$responsablePrenom = $_POST['responsable_prenom'] ?? null;

// Enseignant référent
$enseignantReferent = $_POST['enseignant_referent'] ?? null;

if ($enseignantReferent !== ""){
    $database->setProfessorPreAgreement($idPreConv,$enseignantReferent);
}


$form_data = [
    'nomStudent' => $nomStudent,
    'prenomStudent' => $prenomStudent,
    'numStudent' => $numStudent,
    'formationLevel' => $formationLevel,
    'formation' => $formation,
    'obligation' => $obligation,
    'applicationType' => $applicationType,
    'address' => $address,
    'postalCodeStudent' => $postalCodeStudent,
    'cityStudent' => $cityStudent,
    'phoneStudent' => $phoneStudent,
    'phoneNumberStudent' => $phoneNumberStudent,
    'emailStudent' => $emailStudent,
    'idStudent' => $idStudent,
    'internshipType' => $internshipType,
    'country' => $country,
    'companyName' => $companyName,
    'companyAddress' => $companyAddress,
    'companyPostalCode' => $companyPostalCode,
    'companyCity' => $companyCity,
    'siret' => $siret,
    'ape' => $ape,
    'workforce' => $workforce,
    'legalStatus' => $legalStatus,
    'nomLegal' => $nomLegal,
    'civiliteLegal' => $civiliteLegal,
    'fonctionLegal' => $fonctionLegal,
    'mailLegal' => $mailLegal,
    'nomTuteur' => $nomTuteur,
    'civiliteTuteur' => $civiliteTuteur,
    'fonctionTuteur' => $fonctionTuteur,
    'telTuteur' => $telTuteur,
    'mailTuteur' => $mailTuteur,
    'serviceTuteur' => $serviceTuteur,
    'adresseTuteur' => $adresseTuteur,
    'nomSignataire' => $nomSignataire,
    'civiliteSignataire' => $civiliteSignataire,
    'fonctionSignataire' => $fonctionSignataire,
    'mailSignataire' => $mailSignataire,
    'intershipSubject' => $intershipSubject,
    'tasksFunctions' => $tasksFunctions,
    'pathChoice' => $pathChoice,
    'confidentiality' => $confidentiality,
    'intershipDatesBeginning' => $intershipDatesBeginning,
    'intershipDatesEnding' => $intershipDatesEnding,
    'interruption' => $interruption,
    'interruptionDates' => $interruptionDates,
    'intershipDuration' => $intershipDuration,
    'schedules' => $schedules,
    'schedulesType' => $schedulesType,
    'gratification' => $gratification,
    'monthHour' => $monthHour,
    'salaryType' => $salaryType,
    'paymentType' => $paymentType,
    'dateSignatureEtudiant' => $dateSignatureEtudiant,
    'dateSignatureTuteur' => $dateSignatureTuteur,
    'validation' => $validation,
    'responsableNom' => $responsableNom,
    'responsablePrenom' => $responsablePrenom,
    'enseignantReferent' => $enseignantReferent
];

$inputs_json = json_encode($form_data);


if ($database->getPreAgreementFormById($idPreConv) != null){
    $database->updateInputsPreAgreementStudent($idPreConv, $inputs_json);
}
else{
    $database->insertInputsPreAgreementStudent($inputs_json, $idStudent, null);
}

if ($_POST['action'] == 'action4'){
    $database->setValidMissionsPreAgreement($_POST['id-pre-conv']);
    header('location: Professor.php');
    exit();
}

if ($_POST['action'] == 'action3'){
    $database->setValidPreAgreement($_POST['id-pre-conv']);
    header('location: Secretariat.php');
    exit();
}

if ($role == 1){
    header('location: Student.php');
    exit();
}
else if ($role == 2){
    header('location: Professor.php');
    exit();
}
else if ($role == 3){
    header('location: MaitreStage.php');
    exit();
}
else if ($role == 4){
    header('location: Secretariat.php');
    exit();
}







/*
foreach($form_data as $key => $value){
echo $value;
}
*/
//une fois que c en bdd, on notifie le secretariat, qui va attribuer un tuteur enseignant a l'élève et une fois fait
//$database->addNotification(1, "Un élève à effectué une demande de pré-convention, veuillez lui attribuer un tuteur enseignant", "pré-convention");

?>
