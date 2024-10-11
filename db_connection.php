<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "enservicodb";

// Создаем подключение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем подключение
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Устанавливаем кодировку
$conn->set_charset("utf8");

?>
