<?php
global $person, $database;
$userId = $person->getId();

// Get the groups the user is part of
$groups = $database->getUserGroups($userId);

// Display group chats
if (!empty($groups)) {
    echo "<label><strong>Groupes :</strong></label>";
    foreach ($groups as $group) {
        $groupId = $group['id'];
        $groupName = $group['convention']; // 'convention' is the group name
        echo '<li data-group-id="' . $groupId . '" onclick="openGroupChat(' . $groupId . ', \'' . htmlspecialchars($groupName) . '\')">';
        echo htmlspecialchars($groupName);
        echo '</li>';
    }
}
