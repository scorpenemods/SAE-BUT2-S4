<?php
// Initializing of timestamp format
// Sets the default time zone to "Europe/Paris"
date_default_timezone_set('Europe/Paris');

/**
 * Formats a timestamp into a readable string, displaying "Today", "Yesterday" or the full date.
 *
 * @param string $timestamp The timestamp to format (format recognized by DateTime, for example "2024-04-25 14:30:00").
 * @return string The formatted string representing the date and time.
 */
function formatTimestamp($timestamp): string
{
    // Creates a DateTime object from the provided timestamp
    $date = new DateTime($timestamp);

    // Creates a DateTime object for the current date and time
    $now = new DateTime();

    // Creates a DateTime object for yesterday's date
    $yesterday = new DateTime('yesterday');

    // Compares only the dates (without time) of the message and the current date
    if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
        // If the message was sent today, returns "Today" followed by the time
        return 'Today ' . $date->format('H:i');
    }
    // Compare the date of the message with yesterday
    elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
        // If the message was sent yesterday, returns "Yesterday" followed by the time
        return 'Yesterday ' . $date->format('H:i');
    } else {
        // Otherwise, returns the full date in day.month.year hour:minutes format
        return $date->format('d.m.Y H:i');
    }
}
