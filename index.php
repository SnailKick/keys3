<?php
include 'db_connection.php';
include 'controllers/LicenseController.php';

// Проверьте, что $conn корректно инициализирован
if (!$conn || !$conn instanceof mysqli) {
    die('Ошибка: Подключение к базе данных не инициализировано.');
}

// Инициализация контроллера
$controller = new LicenseController($conn);

// Получение действия из запроса, с защитой от XSS
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'index';

try {
    // Обработка различных действий
    switch ($action) {
        case 'add':
            if (method_exists($controller, 'add')) {
                $controller->add();
            } else {
                throw new Exception('Метод add не существует в контроллере.');
            }
            break;

        case 'create':
            if (method_exists($controller, 'create')) {
                $controller->create();
            } else {
                throw new Exception('Метод create не существует в контроллере.');
            }
            break;

        case 'createRecord':
            if (method_exists($controller, 'createRecord')) {
                $controller->createRecord();
            } else {
                throw new Exception('Метод createRecord не существует в контроллере.');
            }
            break;

        case 'delete':
            if (method_exists($controller, 'delete')) {
                $controller->delete();
            } else {
                throw new Exception('Метод delete не существует в контроллере.');
            }
            break;

        case 'deleteRecord':
            if (method_exists($controller, 'deleteRecord')) {
                $controller->deleteRecord();
            } else {
                throw new Exception('Метод deleteRecord не существует в контроллере.');
            }
            break;

        case 'view':
            if (method_exists($controller, 'view')) {
                $controller->view();
            } else {
                throw new Exception('Метод view не существует в контроллере.');
            }
            break;

        case 'history':
            if (method_exists($controller, 'history')) {
                $controller->history();
            } else {
                throw new Exception('Метод history не существует в контроллере.');
            }
            break;

        case 'check':
            if (method_exists($controller, 'check')) {
                $controller->check();
            } else {
                throw new Exception('Метод check не существует в контроллере.');
            }
            break;

        default:
            if (method_exists($controller, 'index')) {
                $controller->index();
            } else {
                throw new Exception('Метод index не существует в контроллере.');
            }
            break;
    }
} catch (Exception $e) {
    // Обработка исключений и вывод сообщения об ошибке
    echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
}
?>
