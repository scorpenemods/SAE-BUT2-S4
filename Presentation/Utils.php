<?php
// File: Utils.php
// Utility functions

function truncate_UTF8($string, $length) {
    if (strlen($string) <= $length) {
        return $string;
    }

    $truncated = mb_substr($string, 0, $length, 'UTF-8');

    if (str_ends_with($truncated, ' ')) {
        $truncated = substr($truncated, 0, -1);
    }

    $truncated .= '...';

    return $truncated;
}
