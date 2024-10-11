<?php
// load_table.php
header('Content-Type: application/json');
$host = "127.0.0.1";
$db = "root";
$password = "";
$pass = "enservicodb";

$dsn = "mysql:host=$host;dbname=$db;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

// Получаем запрос на поиск (если есть)
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Подготовка SQL запроса
$sql = "SELECT * FROM licenses";

if ($query) {
    $sql .= " WHERE licenc_number LIKE :query OR license_status LIKE :query OR latest_update LIKE :query";
}

try {
    $stmt = $pdo->prepare($sql);

    if ($query) {
        $stmt->bindValue(':query', "%$query%");
    }

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Формирование HTML таблицы
    $html = '<table class="table table-striped" id="licenseTable">';
    $html .= '<thead><tr><th>ID</th><th>Номер лицензии</th><th>Статус лицензии</th><th>Дата обновления</th><th>Actions</th></tr></thead>';
    $html .= '<tbody>';
    
    foreach ($data as $row) {
        $html .= '<tr data-id="' . htmlspecialchars($row['id']) . '">';
        $html .= '<td>' . htmlspecialchars($row['id']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['licenc_number']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['license_status']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['latest_update']) . '</td>';
        $html .= '<td>';
        $html .= '<button class="btn btn-primary edit-btn">Edit</button>';
        $html .= '<button class="btn btn-success save-btn" style="display:none;">Save</button>';
        $html .= '<button class="btn btn-danger delete-btn">Delete</button>';
        $html .= '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';

    echo json_encode(['status' => 'success', 'data' => $html]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database query failed.']);
}
?>
