<?php
class License {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getById($id) {
        $sql = "SELECT * FROM lic_view_full_tabler WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Ошибка подготовки запроса: ' . htmlspecialchars($this->conn->error));
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception('Ошибка выполнения запроса: ' . htmlspecialchars($stmt->error));
        }
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }
}
?>