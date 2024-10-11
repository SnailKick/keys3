<?php
// Подключение к базе данных
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

$connect = mysqli_connect($servername, $username, $password, $dbname);
set_time_limit(0);
if (!$connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$url = "https://iface.ru/check-license/";

$recID = isset($_POST['recID']) ? mysqli_real_escape_string($connect, $_POST['recID']) : null;

if (!$recID) {
    die("Ошибка: Номер лицензии не указан.");
}

// Получаем запись из базы данных по номеру лицензии
$query = "SELECT id, num_lic FROM lic_id WHERE num_lic = ?";
$stmt = $connect->prepare($query);

if (!$stmt) {
    die("Ошибка подготовки запроса: " . $connect->error);
}

$stmt->bind_param('s', $recID);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    die("Ошибка: Запись с номером лицензии " . htmlspecialchars($recID) . " не найдена в таблице lic_id.");
}

$row = $result->fetch_assoc();
$lic_id = $row['id'];

// Проверяем длину лицензионного номера
if (strlen($row["num_lic"]) != 16) {
    die("Ошибка: Неверный лицензионный номер.");
}

$data = array(
    'action' => 'get-license-details',
    'id' => $row["num_lic"],
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

// Устанавливаем статус лицензии по умолчанию на "Ошибка"
$license_status = 'Лицензия аннулирована. Использование лицензии запрещено';

// Обработка ответа
if (isset($media['error'])) {
    $license_status = "<p>" . htmlspecialchars($media['error']) . "</p>";
} elseif (isset($media['complexError'])) {
    if ($media['complexError'] === 'E') {
        $license_status = "<p>Лицензия №" . htmlspecialchars($row['num_lic']) . " аннулирована. Использование лицензии запрещено.</p>";
    } elseif ($media['complexError'] === 'f') {
        $license_status = '<p>При обработке в базе лицензий возникла техническая проблема. <br />Пожалуйста, обратитесь в <a href="/support/">службу технической поддержки</a>.</p>';
    }
} else {
    if (isset($media['message']) && empty($media['message'])) {
        $license_status = '<p>Лицензия специальной версии ОИК Диспетчер НТ РТС распространяется компанией <br />АО «РТСофт». Для обработки заказа заявку следует направлять в АО «РТСофт» <br />тел. +7 (495) 967-1505, E-mail: <a href="mailto:ko@rtsoft.ru">ko@rtsoft.ru</a>.</p>';
    } else {
        if ((isset($media['message'][1]) && !empty($media['message'][1]['value'])) || 
            (isset($media['message'][2]) && !empty($media['message'][2]['value']))) {
            $license_status = 'Действительна';
        }
    }
}

// Функция для преобразования формата даты
function formatDate($date) {
    $dateTime = DateTime::createFromFormat('d.m.Y', $date);
    return $dateTime ? $dateTime->format('Y-m-d') : null;
}

// Инициализация переменных для данных лицензии
$platform = '';
$date_sub_upgrade = '';
$date_creation = '';
$volume_parameters = '';
$users = '';
$max_soft_version = '';
$client = '';
$client10 = '';
$web_client10 = '';
$protocol_support = '';
$opc_support = '';
$web_client_support = '';
$exchange_protocol_dnp3 = '';
$security_level = '';

// Обрабатываем данные JSON и заполняем переменные
if (isset($media['message'])) {
    foreach ($media['message'] as $item) {
        $name = trim($item['name']);
        $value = mysqli_real_escape_string($connect, $item['value']);

        switch ($name) {
            case 'Платформа':
                $platform = $value;
                break;
            case 'Дата разрешенного обновления до':
                $date_sub_upgrade = formatDate($value); 
                break;
            case 'Дата создания лицензии':
                $date_creation = formatDate($value);
                break;
            case 'Количество регистрируемых телепараметров':
                $volume_parameters = $value;
                break;
            case 'Количество рабочих станций':
                $users = $value;
                break;
            case 'Максимальная версия ПО':
                $max_soft_version = $value;
                break;
            case 'Клиент':
                $client = $value;
                break;
            case 'Клиент10':
                $client10 = $value;
                break;
            case 'Веб-клиент10':
                $web_client10 = $value;
                break;
            case 'Поддержка протокола МЭК 60870-5-103':
                $protocol_support = $value;
                break;
            case 'Поддержка OPC DA2.0 клиент/сервер':
                $opc_support = $value;
                break;
            case 'Поддержка Веб-клиента':
                $web_client_support = $value;
                break;
            case 'Протокол обмена DNP3':
                $exchange_protocol_dnp3 = $value;
                break;
            case 'Уровень безопасности':
                $security_level = $value;
                break;
        }
    }
}

// Если максимальная версия ПО пустая, обновляем статус лицензии
if (empty($max_soft_version)) {
    $license_status = 'Лицензия аннулирована. Использование лицензии запрещено';
    echo "Не удалось создать запись: Лицензия аннулирована. Использование лицензии запрещено";
    exit();
}

// Проверяем наличие всех необходимых данных перед вставкой новой записи
if ($license_status == '<p>Лицензия не найдена.</p>') {
    echo "Не удалось создать запись: Лицензия не найдена";
    exit();
}

// Обновляем старую запись (если существует)
$update_query = "UPDATE lic_param SET actual = 1 WHERE lic_id = ?";
$stmt = $connect->prepare($update_query);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $connect->error);
}
$stmt->bind_param('i', $lic_id);
$stmt->execute();
$stmt->close();

// Обновляем статус лицензии в таблице lic_id
$update_status = "UPDATE lic_id SET text_lic = ? WHERE id = ?";
$stmt = $connect->prepare($update_status);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $connect->error);
}
$stmt->bind_param('si', $license_status, $lic_id);
$stmt->execute();
$stmt->close();

// Вставляем новую запись
$insert_query = "INSERT INTO lic_param 
    (lic_id, volume_parameters, users, platform, date_creation, date_sub_upgrade, max_soft_version, client, client10, web_client10, protocol_support, opc_support, web_client_support, exchange_protocol_dnp3, security_level, date_latest_update, actual) 
    VALUES 
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";

$stmt = $connect->prepare($insert_query);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $connect->error);
}
$stmt->bind_param('issssssssssssss', $lic_id, $volume_parameters, $users, $platform, $date_creation, $date_sub_upgrade, $max_soft_version, $client, $client10, $web_client10, $protocol_support, $opc_support, $web_client_support, $exchange_protocol_dnp3, $security_level);

if ($stmt->execute()) {
    echo "<p>Данные лицензии обновлены:</p><br>";

    echo "<div style='font-family: Arial, sans-serif; font-size: 16px;'>";

    $data = array(
			'Лицензия' => $recID,
			'Максимальная версия ПО' => $max_soft_version,
			'Количество телепараметров' => $volume_parameters,
			'Количество пользователей' => $users,
			'Платформа' => $platform,
			'Дата создания' => $date_creation,
			'Дата разрешенного обновления' => $date_sub_upgrade,
			'Клиент' => $client,
			'Клиент 10' => $client10,
			'Веб-клиент 10' => $web_client10,
			'Поддержка протокола МЭК 60870-5-103' => $protocol_support,
			'Поддержка OPC DA2.0' => $opc_support,
			'Поддержка веб-клиента' => $web_client_support,
			'Протокол обмена DNP3' => $exchange_protocol_dnp3,
			'Уровень безопасности' => $security_level,
		);

    foreach ($data as $label => $value) {
        echo "<p style='margin: 0; padding: 5px 0;'><strong>$label:</strong> <span style='color: red;'>$value</span></p>";
    }

    echo "</div>";
} else {
    echo "<p>Ошибка вставки записи: " . $stmt->error . "</p>";
}

$stmt->close();
mysqli_close($connect);
?>
