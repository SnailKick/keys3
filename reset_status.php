<?php
header('Content-Type: application/json');

// Параметры подключения к базе данных
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$response = [];

if (isset($_POST['num_lic']) && !empty($_POST['num_lic'])) {
    $num_lic = trim($_POST['num_lic']); // Удаляем пробелы

    // Начинаем транзакцию
    $conn->begin_transaction();

    try {
        // Обновление данных в таблице lic_id
        $sql_update_lic_id = "UPDATE lic_id SET actual = 0 WHERE num_lic = ? AND actual = 3";
        $stmt_update_lic_id = $conn->prepare($sql_update_lic_id);
        if (!$stmt_update_lic_id) {
            throw new Exception("Ошибка подготовки запроса lic_id: " . $conn->error);
        }
        $stmt_update_lic_id->bind_param("s", $num_lic);
        $stmt_update_lic_id->execute();
        $stmt_update_lic_id->close();

        // Обновление данных в таблице lic_param
        $sql_update_lic_param = "UPDATE lic_param SET actual = 0 WHERE num_lic = ? AND actual = 3";
        $stmt_update_lic_param = $conn->prepare($sql_update_lic_param);
        if (!$stmt_update_lic_param) {
            throw new Exception("Ошибка подготовки запроса lic_param: " . $conn->error);
        }
        $stmt_update_lic_param->bind_param("s", $num_lic);
        $stmt_update_lic_param->execute();
        $stmt_update_lic_param->close();

        // Подтверждаем транзакцию
        $conn->commit();

        // Успех, возвращаем JSON
        $response['status'] = 'success';
        $response['message'] = 'Статус лицензии успешно сброшен';
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        $conn->rollback();

        $response['status'] = 'error';
        $response['message'] = 'Ошибка при сбросе статуса лицензии: ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Некорректные данные.';
}

echo json_encode($response);
$conn->close();
?>
