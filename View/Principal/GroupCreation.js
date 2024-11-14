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

    // Vérification de l'existence du formulaire avant d'ajouter l'écouteur d'événement
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

// Function to delete a group
function deleteGroup(groupId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?')) {
        fetch('../View/Principal/DeleteGroup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ group_id: groupId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Groupe supprimé avec succès.');
                    window.location.reload();
                } else {
                    alert('Erreur lors de la suppression du groupe.');
                }
            })
            .catch(error => console.error('Erreur:', error));
    }
}

// Function to open the edit group modal
function openEditGroupModal(groupId) {
    // Fetch group details and pre-fill the form
    fetch('../View/Principal/GetGroupDetails.php?group_id=' + groupId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Open the modal and pre-fill the form with existing members
                const editGroupModal = document.getElementById('editGroupModal');
                editGroupModal.style.display = 'flex';

                document.getElementById('edit-group-id').value = groupId;

                // Pre-select members in the form
                document.getElementById('edit-student-select').value = data.members.student_id;
                document.getElementById('edit-professor-select').value = data.members.professor_id;
                document.getElementById('edit-maitre-select').value = data.members.maitre_id;

            } else {
                alert('Erreur lors de la récupération des détails du groupe.');
            }
        })
        .catch(error => console.error('Erreur:', error));
}