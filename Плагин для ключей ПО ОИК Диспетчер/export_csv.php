<?php
header('Content-Type: application/json');

// Параметры подключения к базе данных
$servername = "локальный ip адрес";
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

$response = array();

// Проверка наличия необходимых данных
if (isset($_POST['num_lic'])) {
    $num_lic = trim($_POST['num_lic']); // Удаляем пробелы
    if (!empty($num_lic)) {
        // Дополнительные данные для вставки
        $the_end_user = isset($_POST['the_end_user']) ? trim($_POST['the_end_user']) : '';
        $object = isset($_POST['object']) ? trim($_POST['object']) : '';
        $record_link = isset($_POST['record_link']) ? trim($_POST['record_link']) : '';
        $record_name = isset($_POST['record_name']) ? trim($_POST['record_name']) : '';
        $name_count = isset($_POST['name_count']) ? trim($_POST['name_count']) : '';

        // Выводим данные для отладки
        error_log("num_lic: " . $num_lic);
        error_log("the_end_user: " . $the_end_user);
        error_log("object: " . $object);
        error_log("record_link: " . $record_link);
        error_log("record_name: " . $record_name);
        error_log("name_count: " . $name_count);

        // Проверка на существование лицензии
        $stmt = $conn->prepare("SELECT id, actual FROM lic_id WHERE num_lic = ?");
        $stmt->bind_param("s", $num_lic);
        $stmt->execute();
        $result_check = $stmt->get_result();

        if (!$result_check) {
            echo json_encode(array('status' => 'error', 'message' => 'Ошибка выполнения запроса проверки лицензии: ' . $conn->error));
            exit();
        }

        if ($result_check->num_rows > 0) {
            // Лицензия существует
            $license = $result_check->fetch_assoc();

            if ($license['actual'] == 3) {
                // Лицензия существует и actual = 3, перезаписываем
                $conn->autocommit(false);
                $conn->begin_transaction();

                try {
                    // Проверка существования именно такого же пути к лицензии в базе данных
                    $stmt = $conn->prepare("SELECT id FROM lic_record WHERE id = ? AND record_link = ?");
                    $stmt->bind_param("is", $license['id'], $record_link);
                    $stmt->execute();
                    $result_check_lic_record = $stmt->get_result();

                    if ($result_check_lic_record->num_rows == 0) {
                        // Путь к лицензии не существует, добавляем новую запись
                        $stmt = $conn->prepare("INSERT INTO lic_record (id, record_link, record_name, name_count, date_insert) VALUES (?, ?, ?, ?, NOW())");
                        $stmt->bind_param("isss", $license['id'], $record_link, $record_name, $name_count);
                        if (!$stmt->execute()) {
                            throw new Exception("Ошибка выполнения запроса вставки lic_record: " . $conn->error);
                        }
                    }

                    // Обновление данных в таблице lic_id
                    $stmt = $conn->prepare("UPDATE lic_id SET actual = 0 WHERE num_lic = ? AND actual = 3");
                    $stmt->bind_param("s", $num_lic);
                    if (!$stmt->execute()) {
                        throw new Exception("Ошибка выполнения запроса lic_id: " . $conn->error);
                    }

                    // Обновление данных в таблице lic_param
                    $stmt = $conn->prepare("UPDATE lic_param SET actual = 0 WHERE lic_id = ? AND actual = 3");
                    $stmt->bind_param("i", $license['id']);
                    if (!$stmt->execute()) {
                        throw new Exception("Ошибка выполнения запроса lic_param: " . $conn->error);
                    }

                    // Подтверждаем транзакцию
                    $conn->commit();

                    // Успех, возвращаем JSON с ID
                    $response['status'] = 'exists';
                    $response['message'] = 'Удаленная лицензия восстановлена.';
                    $response['id'] = $license['id'];
                } catch (Exception $e) {
                    // Откат транзакции в случае ошибки
                    $conn->rollback();

                    $response['status'] = 'error';
                    $response['message'] = 'Ошибка при обновлении лицензии: ' . $e->getMessage();
                }

                $conn->autocommit(true);
            } else {
                $stmt = $conn->prepare("SELECT id FROM lic_record WHERE id = ? AND record_link = ?");
                $stmt->bind_param("is", $license['id'], $record_link);
                $stmt->execute();
                $result_check_lic_record = $stmt->get_result();

                if ($result_check_lic_record->num_rows == 0) {
                    // Путь к лицензии не существует, добавляем новую запись
                    $stmt = $conn->prepare("INSERT INTO lic_record (id, record_link, record_name, name_count, date_insert) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("isss", $license['id'], $record_link, $record_name, $name_count);
                    if (!$stmt->execute()) {
                        throw new Exception("Ошибка выполнения запроса вставки lic_record: " . $conn->error);
                    }
                }
                // Подтверждаем транзакцию
                $conn->commit();
                // Лицензия существует, возвращаем сообщение и ID
                $response['status'] = 'exists';
                $response['message'] = '';
                $response['id'] = $license['id'];
            }
        } else {
            // Лицензия не найдена, создаем новую запись

            // Проверяем лицензию через внешний API
            $url = "https://iface.ru/check-license/";
            $data = array(
                'action' => 'get-license-details',
                'id' => $num_lic,
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

            $response_data = curl_exec($ch);
            if ($response_data === false) {
                die("Ошибка cURL: " . curl_error($ch));
            }
            $media = json_decode($response_data, true);
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
                    $license_status = "<p>Лицензия №" . htmlspecialchars($num_lic) . " аннулирована. Использование лицензии запрещено.</p>";
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

            if (empty($media['message']) || !is_array($media['message'])) {
                $response['status'] = 'error';
                $response['message'] = 'Не удалось создать запись: Лицензия аннулирована. Использование лицензии запрещено';
                echo json_encode($response);
                exit();
            }

            // Начинаем транзакцию
            $conn->autocommit(false);
            $conn->begin_transaction();

            try {
                // Вставка данных в таблицу lic_id
                $stmt = $conn->prepare("INSERT INTO lic_id (num_lic, the_end_user, object) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $num_lic, $the_end_user, $object);
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка выполнения запроса lic_id: " . $conn->error);
                }

                $lic_id = $conn->insert_id; // Получаем ID вставленной записи

                // Вставка данных в таблицу lic_record
                $stmt = $conn->prepare("INSERT INTO lic_record (id, record_link, record_name, name_count, date_insert) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("isss", $lic_id, $record_link, $record_name, $name_count);
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка выполнения запроса вставки lic_record: " . $conn->error);
                }

                // Обновляем старую запись (если существует)
                $stmt = $conn->prepare("UPDATE lic_param SET actual = 1 WHERE lic_id = ?");
                $stmt->bind_param("i", $lic_id);
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка выполнения запроса: " . $conn->error);
                }

                // Обновляем статус лицензии в таблице lic_id
                $stmt = $conn->prepare("UPDATE lic_id SET text_lic = ? WHERE id = ?");
                $stmt->bind_param("si", $license_status, $lic_id);
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка выполнения запроса: " . $conn->error);
                }

                // Вставляем новую запись в таблицу lic_param
                $volume_parameters = '';
                $users = '';
                $platform = '';
                $date_creation = '';
                $date_sub_upgrade = '';
                $max_soft_version = '';
                $client = '';
                $client10 = '';
                $web_client10 = '';
                $protocol_support = '';
                $opc_support = '';
                $web_client_support = '';
                $exchange_protocol_dnp3 = '';
                $security_level = '';

                foreach ($media['message'] as $item) {
                    $name = $item['name'];
                    $value = $item['value'];
                    switch ($name) {
                        case 'Количество регистрируемых телепараметров':
                            $volume_parameters = $value;
                            break;
                        case 'Количество рабочих станций':
                            $users = $value;
                            break;
                        case 'Платформа':
                            $platform = $value;
                            break;
                        case 'Дата создания лицензии':
                            $date_creation = $value;
                            break;
                        case 'Дата разрешенного обновления до':
                            $date_sub_upgrade = $value;
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

                $stmt = $conn->prepare("INSERT INTO lic_param 
                    (lic_id, volume_parameters, users, platform, date_creation, date_sub_upgrade, max_soft_version, client, client10, web_client10, protocol_support, opc_support, web_client_support, exchange_protocol_dnp3, security_level, date_latest_update, actual) 
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)");
                                $stmt->bind_param("isssssssssssssss", $lic_id, $volume_parameters, $users, $platform, $date_creation, $date_sub_upgrade, $max_soft_version, $client, $client10, $web_client10, $protocol_support, $opc_support, $web_client_support, $exchange_protocol_dnp3, $security_level);

                if ($stmt->execute()) {
                    $conn->commit();
                    // Успех, возвращаем JSON с ID
                    $response['status'] = 'success';
                    $response['message'] = 'Лицензия успешно добавлена';
                    $response['id'] = $lic_id;
                } else {
                    throw new Exception("Ошибка вставки данных в lic_param: " . $conn->error);
                }
            } catch (Exception $e) {
                // Откат транзакции в случае ошибки
                $conn->rollback();
                $response['status'] = 'error';
                $response['message'] = 'Ошибка при добавлении лицензии: ' . $e->getMessage();
            }

            $conn->autocommit(true);
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Некорректные данные формы.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Отсутствует параметр num_lic.';
}

echo json_encode($response);
$conn->close();
?>