<?php
class Record {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll($licenseId) {
        $sql = "SELECT * FROM lic_record WHERE id = ?"; 
        if ($stmt = $this->conn->prepare($sql)) {
            $stmt->bind_param('i', $licenseId);
            if ($stmt->execute()) {
                return $stmt->get_result();
            } else {
                throw new Exception('Ошибка выполнения запроса: ' . htmlspecialchars($stmt->error));
            }
        } else {
            throw new Exception('Ошибка подготовки запроса: ' . htmlspecialchars($this->conn->error));
        }
    }
}
?>