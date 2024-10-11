<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создание соединения
$conn = mysql_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Ошибка подключения: " . mysql_error());
}

// Выбор базы данных
if (!mysql_select_db($dbname, $conn)) {
    die("Ошибка выбора базы данных: " . mysql_error());
}

// Устанавливаем кодировку
mysql_set_charset("utf8", $conn);

// Получаем данные из POST-запроса
$data = array(
    'num_lic' => isset($_POST["num_lic"]) ? $_POST["num_lic"] : null,
    'date_latest_update' => isset($_POST["date_latest_update"]) ? $_POST["date_latest_update"] : null,
    'the_end_user' => isset($_POST["the_end_user"]) ? $_POST["the_end_user"] : null,
    'object' => isset($_POST["object"]) ? $_POST["object"] : null,
    'record_name' => isset($_POST["record_name"]) ? $_POST["record_name"] : null,
    'record_link' => isset($_POST["record_link"]) ? $_POST["record_link"] : null,
    'name_count' => isset($_POST["name_count"]) ? $_POST["name_count"] : null
);

if (!$data['num_lic']) {
    echo json_encode(array("status" => "error", "message" => "Ошибка: Номер лицензии не указан."));
    exit();
}

// Отключаем автокоммит
mysql_query("SET AUTOCOMMIT=0", $conn);
mysql_query("START TRANSACTION", $conn);

try {
    // Проверяем наличие записи в таблице lic_id
    $num_lic = mysql_real_escape_string($data['num_lic'], $conn);
    $query_check = "SELECT id FROM lic_id WHERE num_lic = '$num_lic'";
    $result = mysql_query($query_check, $conn);

    if (!$result) {
        throw new Exception("Ошибка выполнения запроса проверки наличия записи: " . mysql_error($conn));
    }

    if (mysql_num_rows($result) == 0) {
        throw new Exception("Ошибка: Запись с номером лицензии " . htmlspecialchars($num_lic) . " не найдена в таблице lic_id.");
    }

    $row = mysql_fetch_assoc($result);
    $lic_id = $row['id']; // Используем id из таблицы lic_id

    // Обновляем данные в таблице lic_id
    $object = mysql_real_escape_string($data['object'], $conn);
    $the_end_user = mysql_real_escape_string($data['the_end_user'], $conn);
    $query_update = "UPDATE lic_id SET object = '$object', the_end_user = '$the_end_user' WHERE id = '$lic_id'";
    if (!mysql_query($query_update, $conn)) {
        throw new Exception("Ошибка выполнения запроса на обновление: " . mysql_error($conn));
    }

    // Вставляем новую строку в таблицу lic_record
    $record_name = mysql_real_escape_string($data['record_name'], $conn);
    $record_link = mysql_real_escape_string($data['record_link'], $conn);
    $name_count = mysql_real_escape_string($data['name_count'], $conn);
    $query_insert = "INSERT INTO lic_record (id, record_name, record_link, name_count, date_insert) VALUES ('$lic_id', '$record_name', '$record_link', '$name_count', NOW())";
    if (!mysql_query($query_insert, $conn)) {
        throw new Exception("Ошибка выполнения запроса на вставку: " . mysql_error($conn));
    }

    // Подтверждаем транзакцию
    if (!mysql_query("COMMIT", $conn)) {
        throw new Exception("Ошибка при коммите транзакции: " . mysql_error($conn));
    }

    echo json_encode(array("status" => "success", "message" => "Данные успешно сохранены"));
} catch (Exception $e) {
    // Откатываем изменения в случае ошибки
    mysql_query("ROLLBACK", $conn);
    echo json_encode(array("status" => "error", "message" => "Произошла ошибка: " . $e->getMessage()));
}

// Возвращаем автокоммит
mysql_query("SET AUTOCOMMIT=1", $conn);

mysql_close($conn);
?>