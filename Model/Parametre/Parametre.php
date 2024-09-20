<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title</title>
    <link rel="stylesheet" href="Parametre.css">
</head>
<body>
<div class="container">
    <div class="vertical-menu">
        <div class="menu-item" onclick="toggleMenu('account-info', './Information.php')">
            <span>Information du compte</span>
            <span class="arrow">&#9660;</span>
        </div>
        <div class="menu-item" onclick="toggleMenu('preferences', 'Preference.php')">
            <span>Modifier ses préférences</span>
            <span class="arrow">&#9660;</span>
        </div>
    </div>
    <div id="main-content" class="main-content">

    </div>
</div>
<script>
    // Function to toggle the menu content
    function toggleMenu(id, url) {
        const mainContent = document.getElementById('main-content');
        // Make a request to fetch the content from the specified URL
        fetch(url)
            .then(response => response.text())
            .then(data => {
                // Update the main content with the fetched data
                mainContent.innerHTML = data;
            });
    }
</script>
</body>
</html>