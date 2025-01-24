// Show context menu
/*
 * Gère l'affichage du menu contextuel sur les messages.
 * Permet de copier le texte du message ou de le supprimer.
 * Cache le menu lorsqu'on clique en dehors.
 */
document.addEventListener("contextmenu", function (e) {
    const messageElement = e.target.closest('.message');
    if (messageElement) {
        e.preventDefault();
        const contextMenu = document.getElementById("context-menu");
        // Position the context menu near the mouse click
        contextMenu.style.top = e.pageY + "px";
        contextMenu.style.left = e.pageX + "px";
        contextMenu.style.display = "block";
        contextMenu.dataset.messageId = messageElement.dataset.messageId;
        contextMenu.dataset.messageType = messageElement.dataset.messageType || 'private'; // 'private' or 'group'
    } else {
        // Hide context menu if not clicking on a message
        const contextMenu = document.getElementById("context-menu");
        if (contextMenu) {
            contextMenu.style.display = "none";
        }
    }
});

// Hide context menu when clicking elsewhere
document.addEventListener("click", function (e) {
    const contextMenu = document.getElementById("context-menu");
    if (contextMenu && contextMenu.style.display === "block") {
        contextMenu.style.display = "none";
    }
});

// Handler for copying message text
document.getElementById("copy-text").addEventListener("click", function () {
    const contextMenu = document.getElementById("context-menu");
    if (!contextMenu) return;

    const messageId = contextMenu.dataset.messageId;
    const messageContentElement = document.querySelector(`[data-message-id="${messageId}"] p`);
    if (messageContentElement) {
        const messageContent = messageContentElement.innerText;
        navigator.clipboard.writeText(messageContent)
            .then(() => alert("Message copié"))
            .catch(err => console.error("Erreur: ", err));
    }
});

// Handler for deleting a message
document.getElementById("delete-message").addEventListener("click", function () {
    const contextMenu = document.getElementById("context-menu");
    if (!contextMenu) return;

    const messageId = contextMenu.dataset.messageId;
    const messageType = contextMenu.dataset.messageType; // 'private' or 'group'

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "DeleteMessage.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                // Refresh the chat messages
                if (messageType === 'private') {
                    // For private chats
                    updateChat();
                } else if (messageType === 'group') {
                    // For group chats
                    fetchGroupMessages();
                }
            } else {
                alert('Erreur: ' + response.message);
            }
        }
    };
    xhr.send("message_id=" + encodeURIComponent(messageId) + "&message_type=" + encodeURIComponent(messageType));
});