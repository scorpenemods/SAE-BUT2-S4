document.addEventListener("DOMContentLoaded", function () {
    // Show context menu
    document.addEventListener("contextmenu", function (e) {
        e.preventDefault();
        const contextMenu = document.getElementById("context-menu");
        const messageElement = e.target.closest('.message');
        if (messageElement) {
            contextMenu.style.top = e.pageY + "px";
            contextMenu.style.left = e.pageX + "px";
            contextMenu.style.display = "block";
            contextMenu.dataset.messageId = messageElement.dataset.messageId;
            console.log("Context menu opened for message ID:", contextMenu.dataset.messageId);
        } else {
            contextMenu.style.display = "none";
        }
    });

    // Hide context menu when clicking elsewhere
    document.addEventListener("click", function () {
        document.getElementById("context-menu").style.display = "none";
    });

    // Handler for deleting message
    document.getElementById("delete-message").addEventListener("click", function () {
        const contextMenu = document.getElementById("context-menu");
        const messageId = contextMenu.dataset.messageId;
        console.log("Attempting to delete message ID:", messageId);

        // Ensure messageId is valid
        if (!messageId) {
            alert('Message ID is undefined.');
            return;
        }

        // AJAX request to delete message
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_message.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                console.log("Server response:", xhr.responseText);
                if (xhr.responseText.trim() === 'success') {
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageElement) {
                        messageElement.parentNode.removeChild(messageElement);
                        console.log("Message removed from DOM");
                    } else {
                        console.error("Message element not found in DOM");
                    }
                } else {
                    alert('Erreur lors de la suppression du message: ' + xhr.responseText);
                }
            }
        };
        xhr.send("message_id=" + encodeURIComponent(messageId));
    });
});