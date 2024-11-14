// groupMessenger.js

function openGroupChat(groupId, groupName) {
    // Update the chat header with the group name
    document.getElementById('chat-header-title').innerText = 'Chat de groupe : ' + groupName;

    // Save the current group ID
    window.currentGroupId = groupId;
    window.currentChatContactId = null; // Reset private chat contact

    // Change form action to SendGroupMessage.php
    const messageForm = document.getElementById('messageForm');
    messageForm.action = 'SendGroupMessage.php';

    // Set the group_id hidden input
    document.getElementById('group_id').value = groupId;

    // Clear receiver_id value
    document.getElementById('receiver_id').value = '';

    // Fetch group messages
    fetch('../View/Principal/GetGroupMessages.php?group_id=' + groupId)
        .then(response => response.text())
        .then(html => {
            const chatBody = document.getElementById('chat-body');
            chatBody.innerHTML = html;
            chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(error => console.error('Error:', error));
}

// Function to send group message
function sendMessage(event) {
    event.preventDefault();

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    const fileInput = document.getElementById('file-input');
    const groupId = document.getElementById('group_id').value;

    if (!groupId) {
        alert("Veuillez sélectionner un groupe.");
        return;
    }

    if (!message && !fileInput.files.length) {
        alert("Veuillez entrer un message ou sélectionner un fichier.");
        return;
    }

    const formData = new FormData(document.getElementById('messageForm'));

    fetch("SendGroupMessage.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayGroupMessage(
                    data.message,
                    data.file_path,
                    data.sender_id == data.current_user_id ? 'self' : 'other',
                    data.timestamp,
                    data.message_id,
                    data.sender_name
                );
                messageInput.value = '';
                fileInput.value = '';
            } else {
                alert("Erreur: " + data.message);
            }
        })
        .catch(error => console.error("Erreur lors de l'envoi du message: ", error));
}

// Function to display group messages
function displayGroupMessage(messageContent, filePath, messageType, timestamp, messageId, senderName) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', messageType);
    messageElement.dataset.messageId = messageId;

    // Add sender name
    const senderElement = document.createElement('span');
    senderElement.classList.add('sender-name');
    senderElement.textContent = senderName;
    messageElement.appendChild(senderElement);

    // Add message content
    if (messageContent) {
        const messageText = document.createElement('p');
        messageText.innerHTML = messageContent;
        messageElement.appendChild(messageText);
    }

    // Add file link if exists
    if (filePath) {
        const fileLink = document.createElement('a');
        fileLink.href = filePath;
        fileLink.download = true;
        const fileName = filePath.substring(filePath.lastIndexOf('/') + 1);
        fileLink.textContent = fileName || 'Télécharger le fichier';
        messageElement.appendChild(fileLink);
    }

    // Add timestamp
    const timestampContainer = document.createElement('div');
    timestampContainer.classList.add('timestamp-container');
    timestampContainer.innerHTML = `<span class="timestamp">${formatTimestamp(timestamp)}</span>`;
    messageElement.appendChild(timestampContainer);

    const chatBody = document.getElementById('chat-body');
    chatBody.appendChild(messageElement);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Function to periodically update group chat
//setInterval(updateGroupChat, 5000);

function updateGroupChat() {
    const groupId = window.currentGroupId;
    if (!groupId) return;

    fetch('../View/Principal/GetGroupMessages.php?group_id=' + groupId)
        .then(response => response.text())
        .then(html => {
            const chatBody = document.getElementById('chat-body');
            chatBody.innerHTML = html;
            chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(error => console.error('Erreur:', error));
}

// Function to format timestamp (reuse your existing formatTimestamp function)
function formatTimestamp(timestamp) {
    // Implement your timestamp formatting logic here
    return timestamp; // Placeholder
}

// Function to start long polling
function startLongPolling() {
    if (!window.currentGroupId) return;

    let lastTimestamp = window.lastMessageTimestamp || '';

    function poll() {
        fetch(`GetNewGroupMessages.php?group_id=${window.currentGroupId}&last_timestamp=${encodeURIComponent(lastTimestamp)}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const senderId = msg.sender_id;
                        const senderName = msg.sender_name;
                        const messageContent = msg.contenu;
                        const filePath = msg.filepath ? msg.filepath : null;
                        const timestamp = msg.timestamp;
                        const messageId = msg.id;

                        // Update last message timestamp
                        window.lastMessageTimestamp = timestamp;

                        // Determine message type
                        const messageType = (senderId == window.currentUserId) ? 'self' : 'other';

                        displayGroupMessage(
                            messageContent,
                            filePath,
                            messageType,
                            timestamp,
                            messageId,
                            senderName
                        );
                    });
                }

                // Continue polling immediately to check for new messages
                poll();
            })
            .catch(error => {
                console.error('Error in long polling:', error);
                // Retry after a delay if there is an error
                setTimeout(poll, 5000);
            });
    }

    // Start polling
    poll();
}