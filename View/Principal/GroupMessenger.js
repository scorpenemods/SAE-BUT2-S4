// Global variables
window.currentGroupId = null;
let lastGroupMessageTimestamp = null;

// Function to open a group chat
function openGroupChat(groupId, groupName) {
    // Update chat header
    document.getElementById('chat-header-title').innerText = 'Group Chat: ' + groupName;

    // Set current chat context
    window.currentGroupId = groupId;
    window.currentChatContactId = null; // Reset contact ID

    // Set hidden input values
    document.getElementById('group_id').value = groupId;
    document.getElementById('receiver_id').value = '';

    // Update the form action
    const messageForm = document.getElementById('messageForm');
    messageForm.action = 'SendGroupMessage.php';

    // Update active group styling
    const contacts = document.querySelectorAll('#contacts-list li');
    contacts.forEach(contact => {
        contact.classList.remove('contact-active');
    });

    const activeGroup = document.querySelector(`#contacts-list li[data-group-id="${groupId}"]`);
    if (activeGroup) {
        activeGroup.classList.add('contact-active');
    }

    // Fetch and display group messages
    fetchGroupMessages();
}

// Assign sendGroupMessage to window for global access
window.sendGroupMessage = sendGroupMessage;

// Function to fetch and display group messages
function fetchGroupMessages() {
    if (!currentGroupId) return;

    const chatBody = document.getElementById('chat-body');
    const previousScrollHeight = chatBody.scrollHeight;
    const scrollPosition = chatBody.scrollBottom;

    fetch(`../View/Principal/GetGroupMessages.php?group_id=${currentGroupId}`)
        .then(response => response.text())
        .then(html => {
            chatBody.innerHTML = html;

            // Restore scroll position
            chatBody.scrollBottom = scrollPosition;

            // Update last message timestamp
            const messages = chatBody.querySelectorAll('.message');
            if (messages.length > 0) {
                const lastMessage = messages[messages.length - 1];
                const timestampElement = lastMessage.querySelector('.timestamp');
                if (timestampElement) {
                    lastGroupMessageTimestamp = timestampElement.innerText;
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to send group message
function sendGroupMessage(event) {
    event.preventDefault();

    const messageInputElement = $("#message-input").data("emojioneArea");
    const message = messageInputElement ? messageInputElement.getText().trim() : document.getElementById('message-input').value.trim();
    const fileInput = document.getElementById('file-input');

    if (!message && !fileInput.files.length) {
        alert("Please enter a message or select a file.");
        return;
    }

    const formData = new FormData(document.getElementById('messageForm'));
    formData.set('message', message);

    fetch("SendGroupMessage.php", {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (messageInputElement) {
                    messageInputElement.setText(''); // Clear the EmojiOneArea editor
                } else {
                    document.getElementById('message-input').value = '';
                }
                displayGroupMessage(
                    data.message,
                    data.file_path,
                    'self',
                    data.timestamp,
                    data.sender_name
                );
                messageInputElement.value = '';
                fileInput.value = '';
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error sending message: ", error));
}

// Function to display a group message
function displayGroupMessage(messageContent, filePath, messageType, timestamp, senderName) {
    const messageElement = document.createElement('div');
    messageElement.classList.add('message', messageType);
    messageElement.dataset.messageId = Date.now(); // Use a temporary ID or the real message ID if available
    messageElement.dataset.messageType = 'group';

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

    // Add file link or image if exists
    if (filePath) {
        const fileExtension = filePath.split('.').pop().toLowerCase();
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (imageExtensions.includes(fileExtension)) {
            const imageElement = document.createElement('img');
            imageElement.src = filePath;
            imageElement.style.maxWidth = '200px';
            imageElement.style.maxHeight = '200px';
            messageElement.appendChild(imageElement);
        } else {
            const fileLink = document.createElement('a');
            fileLink.href = filePath;
            fileLink.download = true;
            const fileName = filePath.substring(filePath.lastIndexOf('/') + 1);
            fileLink.textContent = fileName || 'Download File';
            messageElement.appendChild(fileLink);
        }
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

// Function to format timestamp
function formatTimestamp(timestamp) {
    const messageDate = new Date(timestamp);
    const now = new Date();

    const isToday = messageDate.toDateString() === now.toDateString();

    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    const isYesterday = messageDate.toDateString() === yesterday.toDateString();

    let formattedTime = messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    if (isToday) {
        return 'Today at ' + formattedTime;
    } else if (isYesterday) {
        return 'Yesterday at ' + formattedTime;
    } else {
        return messageDate.toLocaleDateString() + ' at ' + formattedTime;
    }
}

// Function to check for new group messages
function checkForNewGroupMessages() {
    if (!currentGroupId || !lastGroupMessageTimestamp) return;

    fetch(`GetNewGroupMessages.php?group_id=${currentGroupId}&last_timestamp=${encodeURIComponent(lastGroupMessageTimestamp)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const messageType = (msg.sender_id == currentUserId) ? 'self' : 'other';
                    displayGroupMessage(
                        msg.contenu,
                        msg.filepath,
                        messageType,
                        msg.timestamp,
                        msg.sender_name
                    );
                });

                // Update last message timestamp
                const lastMsg = data.messages[data.messages.length - 1];
                lastGroupMessageTimestamp = lastMsg.timestamp;
            }
        })
        .catch(error => console.error('Error fetching new messages:', error));
}

// Set interval to check for new group messages every 5 seconds
setInterval(checkForNewGroupMessages, 5000);

// Initialize EmojiOneArea if it's included
$(document).ready(function() {
    if ($.fn.emojioneArea) {
        $("#message-input").emojioneArea({
            pickerPosition: "top",
            tonesStyle: "bullet"
        });
    }
});
// Ensure fetchGroupMessages is accessible globally
window.fetchGroupMessages = fetchGroupMessages;