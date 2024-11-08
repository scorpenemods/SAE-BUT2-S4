<?php

$roleMapping = [
    1 => "Etudiant",
    2 => "Professeur",
    3 => "Maitre de stage"
];

// Récupérer les contacts associés à l'utilisateur connecté
$userId = $person->getUserId();
$contacts = $database->getGroupContacts($userId);

// Sort contacts by role
usort($contacts, fn($a, $b) => $a['role'] <=> $b['role']);

// Group contacts by role
$groupedContacts = [];
foreach ($contacts as $contact) {
    $roleName = $roleMapping[$contact['role']] ?? "Unknown Role";
    $groupedContacts[$roleName][] = $contact;
}

// Display contacts grouped by role
foreach ($groupedContacts as $roleName => $contactsGroup) {
    echo "<label><strong>$roleName :</strong></label>";
    foreach ($contactsGroup as $contact) {
        echo '<li data-contact-id="' . $contact['id'] . '" onclick="openChat(' . $contact['id'] . ', \'' . htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']) . '\')">';
        echo htmlspecialchars($contact['prenom'] . ' ' . $contact['nom']);
        echo '</li>';
    }
}
