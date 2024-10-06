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
    } else {
        contextMenu.style.display = "none";
    }
});

// Hide context menu when clicking anywhere else
document.addEventListener("click", function () {
    document.getElementById("context-menu").style.display = "none";
});

// Handler for copying message text
document.getElementById("copy-text").addEventListener("click", function () {
    const messageId = document.getElementById("context-menu").dataset.messageId;
    const messageContent = document.querySelector(`[data-message-id="${messageId}"] p`).innerText;
    navigator.clipboard.writeText(messageContent)
        .then(() => alert("Message copied"))
        .catch(err => console.error("Erreur: ", err));
});

// Handler for deleting a message
document.getElementById("delete-message").addEventListener("click", function () {
    const messageId = document.getElementById("context-menu").dataset.messageId;

    // AJAX Request to Delete a Message
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "delete_message.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            if (xhr.responseText === 'success') {
                document.querySelector(`[data-message-id="${messageId}"]`).remove();
            } else {
                alert('Erreur: ' + xhr.responseText);
            }
        }
    };
    xhr.send("message_id=" + encodeURIComponent(messageId));
});