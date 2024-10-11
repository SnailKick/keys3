<?php
function formatDate($dateTime) {
    if (empty($dateTime)) {
        return '';
    }
    
    // Определите форматы входной даты и времени
    $formatWithTime = 'Y-m-d H:i:s'; // формат с датой и временем
    $formatWithoutTime = 'Y-m-d'; // формат только с датой

    // Попробуем создать объект DateTime из строки
    $dt = DateTime::createFromFormat($formatWithTime, $dateTime);

    if ($dt === false) {
        // Если не удалось распознать формат с временем, попробуем формат только с датой
        $dt = DateTime::createFromFormat($formatWithoutTime, $dateTime);
        if ($dt === false) {
            // Возвращаем исходное значение, если формат не совпадает
            return htmlspecialchars($dateTime);
        }
        // Форматируем только дату
        return htmlspecialchars($dt->format('d.m.Y'));
    }
    
    // Форматируем дату и время
    return htmlspecialchars($dt->format('d.m.Y H:i:s'));
}
?>