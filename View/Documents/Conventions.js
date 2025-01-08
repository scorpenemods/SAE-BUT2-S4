document.addEventListener('DOMContentLoaded', () => {
    const conventionSection = document.querySelector('[data-section="convention"]');

    if (conventionSection){
        const fileInput = document.getElementById('file2');
        const fileLabel = document.getElementById('file-label');
        const uploadForm = document.querySelector('.box');
        const dropZone = document.querySelector('.box__input');
        const fileGrid = document.querySelector('.file-grid');

        let filesToUpload = [];

        // Met à jour le label avec le nom des fichiers
        function updateLabel(fileList) {
            if (fileList.length === 0) {
                fileLabel.textContent = "Aucun fichier choisi";
            } else {
                fileLabel.textContent = Array.from(fileList).map(file => file.name).join(", ");
            }
        }

        // Gestion de la sélection de fichiers via input
        fileInput.addEventListener('change', (event) => {
            filesToUpload = Array.from(event.target.files);
            updateLabel(event.target.files);
        });

        // Empêche le comportement par défaut (ouvrir le fichier) sur dragover/drop
        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (event) => {
            event.preventDefault();
            dropZone.classList.remove('drag-over');

            filesToUpload = Array.from(event.dataTransfer.files);
            updateLabel(event.dataTransfer.files);
        });

        // Charger et afficher les fichiers depuis le serveur
        async function loadFiles() {
            try {
                const response = await fetch('path/to/your/server-endpoint'); // Remplacez par l'URL correcte
                const files = await response.json();

                if (Array.isArray(files)) {
                    fileGrid.innerHTML = ''; // Nettoie la grille
                    files.forEach(file => {
                        const fileCard = document.createElement('div');
                        fileCard.classList.add('file-card');
                        fileCard.innerHTML = `
                        <div class="file-info">
                            <strong>${file.name}</strong>
                            <p>${(file.size / 1024).toFixed(2)} KB</p>
                        </div>
                        <button class="delete-button" data-id="${file.id}">Supprimer</button>
                    `;
                        fileGrid.appendChild(fileCard);
                    });

                    // Ajout des événements de suppression
                    document.querySelectorAll('.delete-button').forEach(button => {
                        button.addEventListener('click', async (event) => {
                            const fileId = event.target.dataset.id;
                            await deleteFile(fileId);
                            loadFiles(); // Recharge la grille après suppression
                        });
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des fichiers :', error);
            }
        }

        // Fonction pour supprimer un fichier
        async function deleteFile(fileId) {
            try {
                const response = await fetch('path/to/your/server-endpoint', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ fileId }),
                });
                const result = await response.json();

                if (!result.success) {
                    alert('Erreur lors de la suppression : ' + result.error);
                }
            } catch (error) {
                console.error('Erreur réseau lors de la suppression :', error);
            }
        }

        // Charger les fichiers au démarrage
        loadFiles();
    }


}

);