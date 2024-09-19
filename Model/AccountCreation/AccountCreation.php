<?php
/*
include 'Service/DB.php';
session_start();


#Si le formulaire est rempli correctement on redirige vers une page de validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('location: accuil.php');
}
*/
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/Model/DefaultStyles/styles.css ">
</head>
<body>

<div class="container">
<h1>Création du compte</h1>
<form action="AccountCreation.php" method="post">
    <p>
        <input type="radio" name="choice" value="student" id="student" required />
        <label for="student">Étudiant</label>

        <input type="radio" name="choice" value="tutorprofessor" id="tutorprofessor" required />
        <label for="tutorprofessor">Professeur referant :</label>

        <input type="radio" name="choice" value="tutorcompany" id="tutorcompany" required />
        <label for="tutorcompany">Tuteur professionnel :</label>
    </p>
    <p>
        <label for="function">Activité professionnelle/universitaire :</label>
        <input name="function" id="function" type="text" required/>
    </p>
    <p>
        <label for="email">E-mail :</label>
        <input name="email" id="email" type="text" required/>
    </p>
    <p>
        <label for="name">Nom :</label>
        <input name="name" id="name" type="text" required/>
    </p>
    <p>
        <label for="firstname">Prénom :</label>
        <input name="firstname" id="firstname" type="text" required/>
    </p>

    <p>
        <label for="phone">Téléphone :</label>
        <input name="phone" id="phone" type="text" required/>
    </p>

    <button type="submit">Valider</button>
</form>
</div>
</body>
</html>