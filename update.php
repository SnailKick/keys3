<?php
include 'db_connection.php';
include 'controllers/LicenseController.php';

$controller = new LicenseController($conn);
$controller->update();
?>
