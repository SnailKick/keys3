<?php
header('Content-Type: application/json');

// Параметры подключения к базе данных
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(array('status' => 'error', 'message' => 'Ошибка подключения: ' . $conn->connect_error)));
}

// Устанавливаем кодировку
$conn->set_charset("utf8");

$response = array();

// Проверка наличия необходимых данных
if (isset($_POST['num_lic']) && isset($_POST['record_link'])) {
    $num_lic = trim($_POST['num_lic']); // Удаляем пробелы
    $record_link = trim($_POST['record_link']); // Удаляем пробелы
    $action = isset($_POST['action']) ? trim($_POST['action']) : ''; // Добавляем параметр action

    if (!empty($num_lic) && filter_var($record_link, FILTER_VALIDATE_URL)) {
        // Проверка на существование лицензии
        $stmt = $conn->prepare("SELECT id, actual FROM lic_id WHERE num_lic = ?");
        if (!$stmt) {
            die(json_encode(array('status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error)));
        }
        $stmt->bind_param("s", $num_lic);
        $stmt->execute();
        $result_check = $stmt->get_result();

        if (!$result_check) {
            die(json_encode(array('status' => 'error', 'message' => 'Ошибка выполнения запроса проверки лицензии: ' . $conn->error)));
        }

        if ($result_check->num_rows > 0) {
            // Лицензия существует
            $license = $result_check->fetch_assoc();
            $response['status'] = 'exists';
            $response['message'] = 'Лицензия уже существует.';
            $response['id'] = $license['id'];
            $response['actual'] = $license['actual'];

            // Проверка на существование пути к задаче
            $stmt = $conn->prepare("SELECT id FROM lic_record WHERE id = ? AND record_link = ?");
            if (!$stmt) {
                die(json_encode(array('status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error)));
            }
            $stmt->bind_param("is", $license['id'], $record_link);
            $stmt->execute();
            $result_check_lic_record = $stmt->get_result();

            if (!$result_check_lic_record) {
                die(json_encode(array('status' => 'error', 'message' => 'Ошибка выполнения запроса проверки пути к задаче: ' . $conn->error)));
            }

            if ($result_check_lic_record->num_rows > 0) {
                $response['message'] = 'Лицензия и путь к задаче существует в базе данных';
            } else {
                $response['message'] = 'Лицензия существует в базе данных, но такого пути к задаче нет.';

                // Если action = 'add_record', добавляем путь к задаче
                if ($action === 'add_record') {
                    $stmt = $conn->prepare("INSERT INTO lic_record (id, record_link, date_insert) VALUES (?, ?, NOW())");
                    if (!$stmt) {
                        die(json_encode(array('status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error)));
                    }
                    $stmt->bind_param("is", $license['id'], $record_link);
                    if ($stmt->execute()) {
                        $response['message'] = 'Путь к задаче успешно добавлен.';
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Ошибка добавления пути к задаче: ' . $conn->error;
                    }
                }
            }
        } else {
            // Лицензия не существует
            $response['status'] = 'not_exists';
            $response['message'] = 'Лицензия не существует.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Некорректные данные формы.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Отсутствуют параметры num_lic или record_link.';
}

echo json_encode($response);
$conn->close();
?>