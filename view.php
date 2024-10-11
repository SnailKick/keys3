<?php
include 'controllers/LicenseController.php';

$controller = new LicenseController($conn);
$controller->view();
?>
