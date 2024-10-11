<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Устанавливаем кодировку
$conn->set_charset("utf8");

// Получаем данные из POST-запроса
$num_lic = isset($_POST["num_lic"]) ? $_POST["num_lic"] : null;
$date_latest_update = isset($_POST["date_latest_update"]) ? $_POST["date_latest_update"] : null;
$actual = isset($_POST["actual"]) ? intval($_POST["actual"]) : 0;

if (!$num_lic) {
    echo json_encode(array("status" => "error", "message" => "Ошибка: Номер лицензии не указан."));
    exit();
}

// Отключаем автокоммит
$conn->autocommit(false);

try {
    // Проверяем наличие записи в таблице lic_id
    $num_lic_escaped = $conn->real_escape_string($num_lic);
    $query_check = "SELECT id FROM lic_id WHERE num_lic = '$num_lic_escaped'";
    $result = $conn->query($query_check);

    if (!$result) {
        throw new Exception("Ошибка выполнения запроса проверки наличия записи: " . $conn->error);
    }

    if ($result->num_rows == 0) {
        throw new Exception("Ошибка: Запись с номером лицензии " . htmlspecialchars($num_lic) . " не найдена в таблице lic_id.");
    }

    $row = $result->fetch_assoc();
    $lic_id = $row['id']; // Используем id из таблицы lic_id

    // Получаем текущую дату и время и добавляем 6 часов
    $current_date_time = date('Y-m-d H:i:s', strtotime('+6 hours'));

    // Вставляем дату изменения в таблицу lic_param
    $lic_id_escaped = $conn->real_escape_string($lic_id);
    $date_latest_update_escaped = $conn->real_escape_string($current_date_time);
    $max_soft_version = "Удалена";
    $max_soft_version_escaped = $conn->real_escape_string($max_soft_version);

    $query_insert = "INSERT INTO lic_param (lic_id, date_latest_update, max_soft_version) VALUES ('$lic_id_escaped', '$date_latest_update_escaped', '$max_soft_version_escaped')";
    if (!$conn->query($query_insert)) {
        throw new Exception("Ошибка выполнения запроса на вставку: " . $conn->error);
    }

    // Обновляем все записи с этим lic_id в таблице lic_param и устанавливаем actual в 3
    $query_update_param = "UPDATE lic_param SET actual = 3 WHERE lic_id = '$lic_id_escaped'";
    if (!$conn->query($query_update_param)) {
        throw new Exception("Ошибка выполнения запроса на обновление lic_param: " . $conn->error);
    }

    // Обновляем actual в таблице lic_id
    $query_update_lic = "UPDATE lic_id SET actual = 3 WHERE id = '$lic_id_escaped'";
    if (!$conn->query($query_update_lic)) {
        throw new Exception("Ошибка выполнения запроса на обновление lic_id: " . $conn->error);
    }

    // Подтверждаем транзакцию
    if (!$conn->commit()) {
        throw new Exception("Ошибка при коммите транзакции: " . $conn->error);
    }

    echo json_encode(array("status" => "success", "message" => "Лицензия была удалена", "date_latest_update" => $current_date_time));
} catch (Exception $e) {
    // Откатываем изменения в случае ошибки
    $conn->rollback();
    echo json_encode(array("status" => "error", "message" => "Произошла ошибка: " . $e->getMessage()));
}

// Возвращаем автокоммит
$conn->autocommit(true);

$conn->close();
?>