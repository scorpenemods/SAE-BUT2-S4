<?php
function formatTimestamp($timestamp) {
    $date = new DateTime($timestamp);
    $now = new DateTime();
    $yesterday = new DateTime('yesterday');

    // Сравнение даты сообщения с сегодняшней датой
    if ($date->format('Y-m-d') == $now->format('Y-m-d')) {
        return 'Today ' . $date->format('H:i');
    }
    // Сравнение даты сообщения со вчерашней датой
    elseif ($date->format('Y-m-d') == $yesterday->format('Y-m-d')) {
        return 'Yesterday ' . $date->format('H:i');
    } else {
        return $date->format('d.m.Y H:i'); // Короткий формат даты и времени
    }
}

?>