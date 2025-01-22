function approveUser(userId) {
    if (confirm("Êtes-vous sûr de vouloir approuver cet utilisateur ?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "ApproveUser.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200 && xhr.responseText.trim() === 'success') {
                alert("Utilisateur approuvé avec succès.");
                location.reload();
            } else {
                alert("Erreur lors de l'approbation de l'utilisateur.");
            }
        };
        xhr.send("user_id=" + encodeURIComponent(userId));
    }
}

function rejectUser(userId) {
    if (confirm("Êtes-vous sûr de vouloir refuser cet utilisateur ?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "RejectUser.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200 && xhr.responseText.trim() === 'success') {
                alert("Utilisateur refusé avec succès.");
                location.reload();
            } else {
                alert("Erreur lors du refus de l'utilisateur.");
            }
        };
        xhr.send("user_id=" + encodeURIComponent(userId));
    }
}

function deleteUser(userId) {
    if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "DeleteUser.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = xhr.responseText.trim();
                if (response === 'success') {
                    alert("Utilisateur supprimé avec succès.");
                    location.reload();
                } else {
                    alert("Erreur lors de la suppression de l'utilisateur: " + response);
                }
            } else {
                alert("Erreur lors de la requête. Code statut: " + xhr.status);
            }
        };
        xhr.send("user_id=" + encodeURIComponent(userId));
    }
}

