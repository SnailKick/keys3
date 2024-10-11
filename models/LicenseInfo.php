<?php
class LicenseInfo {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $sql = "SELECT * FROM lic_view_main_tabler";
        return $this->conn->query($sql);
    }


}
?>
