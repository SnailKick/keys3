<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>История изменений лицензии</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">История изменений лицензии</h2>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Дата изменения</th>
                    <th>Номер договора</th>
                    <th>Путь к задаче</th>
                    <th>Контрагент</th>
                    <th>Конечник</th>
                    <th>Объект</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($license_details) && is_array($license_details)): ?>
                <?php foreach ($license_details as $details): ?>
                    <tr>
                        <td><?php echo formatDate($details['date_insert']); ?></td>
                        <td><?php echo htmlspecialchars($details['record_name']); ?></td>
                        <td><?php echo htmlspecialchars($details['record_link']); ?></td>
                        <td><?php echo htmlspecialchars($details['name_count']); ?></td>
                        <td><?php echo htmlspecialchars($details['the_end_user']); ?></td>
                        <td><?php echo htmlspecialchars($details['object']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">Данные отсутствуют.</td></tr>
            <?php endif; ?>
            </tbody>

        </table>

        <div class="container mt-4" >
            <h2 class="mb-4">Данные лицензии</h2>
            <table class="table table-bordered mt-1" >
    <thead>
        <tr>
            <th>Дата изменения</th>
            <th>Максимальная версия ПО</th>
            <th>Телепараметры</th>
            <th>Количество станций</th>
            <th>Платформа</th>
            <th>Дата создания лицензии</th>
            <th>Дата разрешенного обновления до</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($license_history) && is_array($license_history)): ?>
            <?php foreach ($license_history as $history_item): ?>
            <tr>
                <td><?php echo formatDate($history_item['date_latest_update']); ?></td>
                <td><?php echo htmlspecialchars($history_item['max_soft_version']); ?></td>
                <td><?php echo htmlspecialchars($history_item['volume_parameters']); ?></td>
                <td><?php echo htmlspecialchars($history_item['users']); ?></td>
                <td><?php echo htmlspecialchars($history_item['platform']); ?></td>
                <td><?php echo formatDate($history_item['date_creation']); ?></td>
                <td><?php echo formatDate($history_item['date_sub_upgrade']); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="15">Данные отсутствуют.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

        </div>
    </div>
</body>
</html>
