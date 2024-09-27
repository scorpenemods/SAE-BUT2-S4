<?php
function truncateUTF8($string, $length) {
    if (strlen($string) <= $length) {
        return $string;
    }

    $truncated = mb_substr($string, 0, $length, 'UTF-8');

    if (substr($truncated, -1) === ' ') {
        $truncated = substr($truncated, 0, -1);
    }

    $truncated .= '...';

    return $truncated;
}