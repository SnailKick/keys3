<?php

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создание соединения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получаем номер лицензии из POST-запроса
$recID = isset($_POST['recID']) ? trim($_POST['recID']) : null;

if (!$recID) {
    die("Ошибка: Номер лицензии не указан.");
}

// Проверяем наличие лицензии в базе данных
$query = "SELECT id, num_lic, actual FROM lic_id WHERE num_lic = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}
$stmt->bind_param('s', $recID);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$id = $row['id'];
$actual = $row['actual'];

$stmt->close();

// Внешний API запрос
$url = "https://iface.ru/check-license/";
$data = array(
    'action' => 'get-license-details',
    'id' => $recID,
    'email' => '',
    'url' => ''
);

// Загрузка файла корневых сертификатов
$cacertPath = __DIR__ . '/cacert.pem';
if (!file_exists($cacertPath)) {
    die("Файл сертификатов не найден: $cacertPath");
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded; charset=UTF-8"));
curl_setopt($ch, CURLOPT_CAINFO, $cacertPath);

$response = curl_exec($ch);
if ($response === false) {
    die("Ошибка cURL: " . curl_error($ch));
}
$media = json_decode($response, true);
curl_close($ch);

if (!is_array($media)) {
    die("Ошибка: Неверный формат ответа API.");
}

// Обработка ответа
$license_status = 'Лицензия аннулирована. Использование лицензии запрещено';

if (isset($media['error'])) {
    $license_status = htmlspecialchars($media['error']);
} elseif (isset($media['complexError'])) {
    if ($media['complexError'] === 'E') {
        $license_status = 'Лицензия №' . htmlspecialchars($recID) . ' аннулирована. Использование лицензии запрещено.';
    } elseif ($media['complexError'] === 'f') {
        $license_status = 'При обработке в базе лицензий возникла техническая проблема. Пожалуйста, обратитесь в <a href="/support/">службу технической поддержки</a>.';
    }
} else {
    if (isset($media['message']) && empty($media['message'])) {
        $license_status = 'Лицензия специальной версии ОИК Диспетчер НТ РТС распространяется компанией <br />АО «РТСофт». Для обработки заказа заявку следует направлять в АО «РТСофт» <br />тел. +7 (495) 967-1505, E-mail: <a href="mailto:ko@rtsoft.ru">ko@rtsoft.ru</a>.';
    } else {
        if ((isset($media['message'][1]) && !empty($media['message'][1]['value'])) || 
            (isset($media['message'][2]) && !empty($media['message'][2]['value']))) {
            $license_status = 'Действительна';
        }
    }
}

$data = array(
    'status' => 'success',
    'message' => $license_status,
    'id' => $id,
    'actual' => $actual,
    'details' => array()
);

if (isset($media['message']) && is_array($media['message'])) {
    foreach ($media['message'] as $item) {
        if (isset($item['name']) && isset($item['value'])) {
            $data['details'][] = array(
                'name' => trim($item['name']),
                'value' => htmlspecialchars($item['value'])
            );
        }
    }
}

echo json_encode($data);
?>