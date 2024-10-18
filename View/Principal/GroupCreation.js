// =============================== Groupes ==================================== //
document.addEventListener('DOMContentLoaded', function () {
    const openModalButton = document.querySelector('.open-create-group-modal');
    const closeModalButton = document.querySelector('.close-modal');
    const createGroupModal = document.getElementById('createGroupModal');

    // Ouverture de la fenêtre modale
    if (openModalButton) {
        openModalButton.addEventListener('click', () => {
            createGroupModal.style.display = 'flex';
        });
    }

    // Fermeture de la fenêtre modale
    if (closeModalButton) {
        closeModalButton.addEventListener('click', () => {
            createGroupModal.style.display = 'none';
        });
    }

    // Fermeture de la modale en cliquant à l'extérieur
    window.addEventListener('click', (event) => {
        if (event.target === createGroupModal) {
            createGroupModal.style.display = 'none';
        }
    });

    // **Vérification de l'existence du formulaire avant d'ajouter l'écouteur d'événement**
    const createGroupForm = document.getElementById('createGroupForm');
    if (createGroupForm) {
        // Gestion de la soumission du formulaire
        createGroupForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch('../View/Principal/CreateGroup.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    console.log('Réponse brute du serveur :', text);

                    try {
                        const data = JSON.parse(text);
                        console.log('Réponse JSON analysée :', data);
                        document.getElementById('resultMessage').innerText = data.message;
                        if (data.success) {
                            alert('Le groupe a été créé avec succès.');
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'analyse du JSON :', error);
                        document.getElementById('resultMessage').innerText = 'Erreur lors de la création du groupe.';
                    }
                })
                .catch(error => console.error('Erreur:', error));
        });
    }
});