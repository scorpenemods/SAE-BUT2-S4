<?php //Page d'accès refusé qui s'affiche quand l'utilisateur n'a pas les droits pour une page?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadata for the document -->
    <meta charset="UTF-8"> <!-- Sets character encoding to UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensures the page is responsive on mobile devices -->

    <!-- Link to external stylesheet -->
    <link rel="stylesheet" href="/rebase/Modely/DefaultStyles/styles.css"> <!-- CSS file for page styling -->

    <!-- Page title -->
    <title>Access Denied</title> <!-- Title displayed in the browser tab -->
</head>
<body>
<!-- Centered container for the page content -->
<div style="display: flex; flex-direction: column; justify-content: center; align-items: center">
    <!-- Main title of the page -->
    <h1>Access to this page is denied</h1> <!-- Main message indicating that access is denied -->

    <!-- Information message -->
    <p>You do not have sufficient permissions to view this page.</p> <!-- Explanation for the access denial -->

    <!-- Redirect link to the login page -->
    <a href="../index.php">Log in with another account</a> <!-- Allows the user to log in with another account -->
</div> <!-- End of the centered container -->
</body>

</html>
