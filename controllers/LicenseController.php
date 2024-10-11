<?php
// db_connection.php
// Убедитесь, что этот файл подключается только один раз в вашем проекте

// controllers/LicenseController.php
// Используйте require_once или include_once, чтобы избежать повторного подключения

include_once 'db_connection.php';
include_once 'models/License.php';
include_once 'models/Record.php';
include_once 'models/LicenseInfo.php';
include_once 'models/History.php'; 



class LicenseController {
    private $conn;
    private $license;
    private $record;
    private $history;

    public function __construct($conn) {
        if ($conn instanceof mysqli) {
            $this->conn = $conn;
            $this->license = new License($conn);
            $this->record = new Record($conn);
            $this->history = new History($conn);
        } else {
            throw new Exception('Invalid database connection');
        }
    }
    

    public function index() {
        $sql = "SELECT * FROM lic_view_main_tabler";
        $result = $this->conn->query($sql);
        if ($result === false) {
            die('Ошибка выполнения запроса: ' . htmlspecialchars($this->conn->error));
        }
        $licenses = $result;
        include 'views/index.php';
    }

    public function view() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $license = $this->license->getById($id);
            $records = $this->record->getAll($id);
            
            $license_details = $this->history->getLicenseDetails($id);
            $license_history = $this->history->getLicenseHistory($id);
            if ($license === null) {
                echo 'Лицензия не найдена.';
                return;
            }
            include 'views/view.php';
        } else {
            echo 'Некорректный идентификатор лицензии.';
        }
    }

    
    public function history() { 
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $license_details = $this->history->getLicenseDetails($id);
            $license_history = $this->history->getLicenseHistory($id);
            if ($license_history === null) {
                echo 'Лицензия не найдена.';
                return;
            }
            include 'views/history.php';
        } else {
            echo 'Некорректный идентификатор лицензии.';
        }
    }
}
?>