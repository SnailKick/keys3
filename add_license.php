<?php
header('Content-Type: application/json');

// Параметры подключения к базе данных
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создание соединения
$conn = mysql_connect($servername, $username, $password);
if (!$conn) {
    die("Ошибка подключения: " . mysql_error());
}

// Выбор базы данных
if (!mysql_select_db($dbname, $conn)) {
    die("Ошибка выбора базы данных: " . mysql_error());
}

// Устанавливаем кодировку
mysql_set_charset("utf8", $conn);

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

        // Проверка на существование лицензии
        $num_lic_escaped = mysql_real_escape_string($num_lic, $conn);
        $check_sql = "SELECT id, actual FROM lic_id WHERE num_lic = '$num_lic_escaped'";
        $result_check = mysql_query($check_sql, $conn);

        if (!$result_check) {
            echo json_encode(array('status' => 'error', 'message' => 'Ошибка выполнения запроса проверки лицензии: ' . mysql_error($conn)));
            exit();
        }

        if (mysql_num_rows($result_check) > 0) {
            // Лицензия существует
            $license = mysql_fetch_assoc($result_check);

            if ($license['actual'] == 3) {
                // Лицензия существует и actual = 3, перезаписываем
                mysql_query("SET AUTOCOMMIT=0", $conn);
                mysql_query("START TRANSACTION", $conn);

                try {
                    // Обновление данных в таблице lic_id
                    $sql_update_lic_id = "UPDATE lic_id SET actual = 0 WHERE num_lic = '$num_lic_escaped' AND actual = 3";
                    if (!mysql_query($sql_update_lic_id, $conn)) {
                        throw new Exception("Ошибка выполнения запроса lic_id: " . mysql_error($conn));
                    }

                    // Обновление данных в таблице lic_param
                    $lic_id_escaped = mysql_real_escape_string($license['id'], $conn);
                    $sql_update_lic_param = "UPDATE lic_param SET actual = 0 WHERE lic_id = '$lic_id_escaped' AND actual = 3";
                    if (!mysql_query($sql_update_lic_param, $conn)) {
                        throw new Exception("Ошибка выполнения запроса lic_param: " . mysql_error($conn));
                    }

                    // Вставка новых данных в таблицу lic_record
                    $record_link_escaped = mysql_real_escape_string($record_link, $conn);
                    $record_name_escaped = mysql_real_escape_string($record_name, $conn);
                    $name_count_escaped = mysql_real_escape_string($name_count, $conn);
                    $sql_insert_lic_record = "INSERT INTO lic_record (id, record_link, record_name, name_count, date_insert) VALUES ('$lic_id_escaped', '$record_link_escaped', '$record_name_escaped', '$name_count_escaped', NOW())";
                    if (!mysql_query($sql_insert_lic_record, $conn)) {
                        throw new Exception("Ошибка выполнения запроса вставки lic_record: " . mysql_error($conn));
                    }

                    // Подтверждаем транзакцию
                    if (!mysql_query("COMMIT", $conn)) {
                        throw new Exception("Ошибка при коммите транзакции: " . mysql_error($conn));
                    }

                    // Успех, возвращаем JSON с ID
                    $response['status'] = 'success';
                    $response['message'] = 'Удаленная лицензия восстановлена.';
                    $response['id'] = $license['id'];
                } catch (Exception $e) {
                    // Откат транзакции в случае ошибки
                    mysql_query("ROLLBACK", $conn);

                    $response['status'] = 'error';
                    $response['message'] = 'Ошибка при обновлении лицензии: ' . $e->getMessage();
                }

                mysql_query("SET AUTOCOMMIT=1", $conn);
            } else {
                // Лицензия существует, возвращаем сообщение и ID
                $response['status'] = 'exists';
                $response['message'] = 'Лицензия уже существует.';
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
            mysql_query("SET AUTOCOMMIT=0", $conn);
            mysql_query("START TRANSACTION", $conn);

            try {
                // Вставка данных в таблицу lic_id
                $the_end_user_escaped = mysql_real_escape_string($the_end_user, $conn);
                $object_escaped = mysql_real_escape_string($object, $conn);
                $sql_lic_id = "INSERT INTO lic_id (num_lic, the_end_user, object) VALUES ('$num_lic_escaped', '$the_end_user_escaped', '$object_escaped')";
                if (!mysql_query($sql_lic_id, $conn)) {
                    throw new Exception("Ошибка выполнения запроса lic_id: " . mysql_error($conn));
                }

                $lic_id = mysql_insert_id($conn); // Получаем ID вставленной записи

                // Вставка данных в таблицу lic_record
                $record_link_escaped = mysql_real_escape_string($record_link, $conn);
                $record_name_escaped = mysql_real_escape_string($record_name, $conn);
                $name_count_escaped = mysql_real_escape_string($name_count, $conn);
                $sql_lic_record = "INSERT INTO lic_record (id, record_link, record_name, name_count, date_insert) VALUES ('$lic_id', '$record_link_escaped', '$record_name_escaped', '$name_count_escaped', NOW())";
                if (!mysql_query($sql_lic_record, $conn)) {
                    throw new Exception("Ошибка выполнения запроса вставки lic_record: " . mysql_error($conn));
                }

                // Обновляем старую запись (если существует)
                $update_query = "UPDATE lic_param SET actual = 1 WHERE lic_id = '$lic_id'";
                if (!mysql_query($update_query, $conn)) {
                    throw new Exception("Ошибка выполнения запроса: " . mysql_error($conn));
                }

                // Обновляем статус лицензии в таблице lic_id
                $license_status_escaped = mysql_real_escape_string($license_status, $conn);
                $update_status = "UPDATE lic_id SET text_lic = '$license_status_escaped' WHERE id = '$lic_id'";
                if (!mysql_query($update_status, $conn)) {
                    throw new Exception("Ошибка выполнения запроса: " . mysql_error($conn));
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

                foreach ($media['message'] as $item) {
                    $name = trim($item['name']);
                    $value = mysql_real_escape_string($item['value'], $conn);

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

                // Проверка, что дата разрешенного обновления до не пуста
                if (empty($date_sub_upgrade)) {
                    throw new Exception("Дата разрешенного обновления до не найдена в ответе API.");
                }

                // Вставка данных в таблицу lic_param
                $insert_query = "INSERT INTO lic_param 
                    (lic_id, volume_parameters, users, platform, date_creation, date_sub_upgrade, max_soft_version, client, client10, web_client10, protocol_support, opc_support, web_client_support, exchange_protocol_dnp3, security_level, date_latest_update, actual) 
                    VALUES 
                    ('$lic_id', '$volume_parameters', '$users', '$platform', '$date_creation', '$date_sub_upgrade', '$max_soft_version', '$client', '$client10', '$web_client10', '$protocol_support', '$opc_support', '$web_client_support', '$exchange_protocol_dnp3', '$security_level', NOW(), 0)";

                if (mysql_query($insert_query, $conn)) {
                    mysql_query("COMMIT", $conn);
                    // Успех, возвращаем JSON с ID
                    $response['status'] = 'success';
                    $response['message'] = 'Лицензия успешно добавлена';
                    $response['id'] = $lic_id;
                } else {
                    throw new Exception("Ошибка вставки данных в lic_param: " . mysql_error($conn));
                }
            } catch (Exception $e) {
                // Откат транзакции в случае ошибки
                mysql_query("ROLLBACK", $conn);
                $response['status'] = 'error';
                $response['message'] = 'Ошибка при добавлении лицензии: ' . $e->getMessage();
            }

            mysql_query("SET AUTOCOMMIT=1", $conn);
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Некорректные данные формы.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Некорректные данные формы.';
}

echo json_encode($response);
mysql_close($conn);
?>