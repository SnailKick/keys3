<?php
class History {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Получение деталей лицензии
    public function getLicenseDetails($id) {
        $sql = "SELECT lic_record.*, lic_id.*
                FROM lic_record
                LEFT JOIN lic_id ON lic_record.id = lic_id.id
                WHERE lic_record.id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Ошибка подготовки запроса: ' . htmlspecialchars($this->conn->error));
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception('Ошибка выполнения запроса: ' . htmlspecialchars($stmt->error));
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // Получение истории лицензии
    public function getLicenseHistory($id) {
        $sql = "SELECT *
                FROM lic_param
                WHERE lic_param.lic_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Ошибка подготовки запроса: ' . htmlspecialchars($this->conn->error));
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception('Ошибка выполнения запроса: ' . htmlspecialchars($stmt->error));
        }
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

}
?>
