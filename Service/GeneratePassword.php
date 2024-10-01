<?php

// Function that generates a temporary password for account validation by the secretary
// Awaiting the user's password input

function generate_password() {
    // Define character sets to use
    $lowercaseLetters = 'abcdefghijklmnopqrstuvwxyz';
    $uppercaseLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $symbols = '!@#$%^&*()-_+=<>?';

    // Combine all character sets
    $allCharacters = $lowercaseLetters . $uppercaseLetters . $numbers . $symbols;

    // Define the length of the random password between 8 and 12 characters
    $passwordLength = rand(8, 12);

    // Shuffle all characters
    $allCharacters = str_shuffle($allCharacters);

    // Generate the password
    $password = '';
    for ($i = 0; $i < $passwordLength; $i++) {
        $password .= $allCharacters[rand(0, strlen($allCharacters) - 1)];
    }

    return $password;
}

// Example usage
echo generate_password();

?>
