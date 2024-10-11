<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=exported_licenses.csv');

$connect = mysqli_connect('127.0.0.1', 'root', '', 'enservicodb');

if (!$connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$query = "SELECT 
    `lic_id`.`id` AS `id`,
    `lic_id`.`num_lic` AS `Номер_лицензии`,
    `lic_id`.`text_lic` AS `Состояние_лицензии`,
    `lic_id`.`the_end_user` AS `Конечник`,
    `lic_id`.`object` AS `Объект`,
    `p`.`volume_parameters` AS `volume_parameters`,
    `p`.`users` AS `users`,
    `p`.`platform` AS `platform`,
    `p`.`date_creation` AS `date_creation`,
    `p`.`date_sub_upgrade` AS `date_sub_upgrade`,
    `p`.`date_latest_update` AS `date_latest_update`,
    `p`.`max_soft_version` AS `max_soft_version`,
    `p`.`client` AS `client`,
    `p`.`client10` AS `client10`,
    `p`.`web_client10` AS `web_client10`,
    `p`.`protocol_support` AS `protocol_support`,
    `p`.`opc_support` AS `opc_support`,
    `p`.`web_client_support` AS `web_client_support`,
    `p`.`exchange_protocol_dnp3` AS `exchange_protocol_dnp3`,
    `p`.`security_level` AS `security_level`,
    `r`.`record_name` AS `record_name`,
    `r`.`record_link` AS `record_link`,
    `r`.`name_count` AS `name_count`,
    `r`.`date_insert` AS `date_insert`,
    `r`.`id` AS `record_id`
FROM 
    `lic_id`
LEFT JOIN 
    `lic_view_actual_param` `p` ON `lic_id`.`id` = `p`.`lic_id`
LEFT JOIN 
    `lic_record` `r` ON (`lic_id`.`id` = `r`.`id` AND `r`.`date_insert` = (SELECT MAX(`lr`.`date_insert`) FROM `lic_record` `lr` WHERE `lr`.`id` = `lic_id`.`id`))
ORDER BY 
    (CASE 
        WHEN `lic_id`.`text_lic` = 'Лицензия аннулирована. Использование лицензии запрещено.' THEN 3
        WHEN `lic_id`.`text_lic` = '<p>Лицензия не найдена.</p>' THEN 4
        ELSE 1 
    END),
    `p`.`date_creation` DESC";

$result = mysqli_query($connect, $query);
if (!$result) {
    die("Ошибка выполнения запроса к базе данных: " . mysqli_error($connect));
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=data.csv');

$output = fopen('php://output', 'w');

// Добавление BOM для корректной интерпретации UTF-8 в Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Установка заголовков
fputcsv($output, array(
    'ID', 'Номер лицензии', 'Состояние лицензии', 'Конечник', 'Объект', 'Объем параметров', 'Количество пользователей', 'Платформа', 'Дата создания', 'Дата разрешенного обновления', 'Дата последнего обновления', 'Максимальная версия ПО', 'Клиент', 'Клиент 10', 'Веб-клиент 10', 'Поддержка протокола', 'Поддержка OPC', 'Поддержка веб-клиента', 'Протокол обмена DNP3', 'Уровень безопасности', 'Название записи', 'Ссылка на запись', 'Имя контрагента', 'Дата вставки', 'ID записи'
), ';');

// Заполнение данных из базы данных
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['id'],
        '\''.$row['Номер_лицензии'],
        $row['Состояние_лицензии'],
        $row['Конечник'],
        $row['Объект'],
        $row['volume_parameters'],
        $row['users'],
        $row['platform'],
        $row['date_creation'],
        $row['date_sub_upgrade'],
        $row['date_latest_update'],
        $row['max_soft_version'],
        $row['client'],
        $row['client10'],
        $row['web_client10'],
        $row['protocol_support'],
        $row['opc_support'],
        $row['web_client_support'],
        $row['exchange_protocol_dnp3'],
        $row['security_level'],
        '\''.$row['record_name'],
        $row['record_link'],
        $row['name_count'],
        $row['date_insert']
    ), ';');
}

fclose($output);
mysqli_close($connect);
exit();
?>