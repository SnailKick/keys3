<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали лицензии № <?php echo htmlspecialchars($license['Номер_лицензии']); ?></title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tippy.js@6/dist/tippy.css" rel="stylesheet">
    <style>
        #licenseDetails {
            table-layout: fixed;
            width: 100%;
        }

        #licenseDetails th:first-child,
        #licenseDetails td:first-child {
            width: 300px; /* Фиксированная ширина для первого столбца */
            white-space: nowrap; /* Предотвращает перенос текста */
            overflow: hidden; /* Скрывает текст, который не помещается */
            text-overflow: ellipsis; /* Добавляет многоточие для обрезанного текста */
        }
    </style>
</head>

<body>
    <?php include_once './formatDateTime.php'; ?>
    <div class="container mt-4">
        <h1 class="mb-4">Детали лицензии № <?php echo htmlspecialchars($license['Номер_лицензии']); ?></h1>
		<div class="d-flex justify-content-end mb-4">
			<button class="btn btn-secondary me-4" id="backBtn">Назад</button>
			<button class="btn btn-danger btn-sm me-4" id="delete-btn">Удалить</button>
			<button class="btn btn-success btn-sm check-btn me-4" id="checkBtn">Проверить</button>
			<button class="btn btn-primary" id="saveChangesBtn">Сохранить</button>
		</div>


        <!-- License Details Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Информация</h5>
            </div>
			
            <div class="card-body">
                <table class="table table-striped" id="licenseDetails">
                    <tbody>
                        <tr class="str"><th data-copy="true">Номер лицензии</th><td contenteditable="false"><?php echo htmlspecialchars($license['Номер_лицензии']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Состояние лицензии</th><td contenteditable="false"><?php echo htmlspecialchars($license['Состояние_лицензии']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Дата последнего обновления</th><td contenteditable="false"><?php echo formatDate($license['date_latest_update']); ?></td></tr>
                        <tr class="string_update"><th data-copy="true">Номер договора</th><td contenteditable="true"><?php echo htmlspecialchars($license['record_name']); ?></td></tr>
                        <tr class="string_update"><th data-copy="true">Путь к задаче</th><td contenteditable="true"><?php echo htmlspecialchars($license['record_link']); ?></td></tr>
                        <tr class="string_update"><th data-copy="true">Контрагент</th><td contenteditable="true"><?php echo htmlspecialchars($license['name_count']); ?></td></tr>
                        <tr class="string_update"><th data-copy="true">Конечник</th><td contenteditable="true"><?php echo htmlspecialchars($license['Конечник']); ?></td></tr>
                        <tr class="string_update"><th data-copy="true">Объект</th><td contenteditable="true"><?php echo htmlspecialchars($license['Объект']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Максимальная версия ПО</th><td contenteditable="false"><?php echo htmlspecialchars($license['max_soft_version']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Телепараметры</th><td contenteditable="false"><?php echo htmlspecialchars($license['volume_parameters']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Количество станций</th><td contenteditable="false"><?php echo htmlspecialchars($license['users']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Платформа</th><td contenteditable="false"><?php echo htmlspecialchars($license['platform']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Дата создания лицензии</th><td contenteditable="false"><?php echo formatDate($license['date_creation']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Дата разрешенного обновления до</th><td contenteditable="false"><?php echo formatDate($license['date_sub_upgrade']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Клиент</th><td contenteditable="false"><?php echo htmlspecialchars($license['client']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Клиент10</th><td contenteditable="false"><?php echo htmlspecialchars($license['client10']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Веб-клиент10</th><td contenteditable="false"><?php echo htmlspecialchars($license['web_client10']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Поддержка протокола МЭК</th><td contenteditable="false"><?php echo htmlspecialchars($license['protocol_support']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Поддержка OPC DA2.0</th><td contenteditable="false"><?php echo htmlspecialchars($license['opc_support']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Поддержка Веб-клиента</th><td contenteditable="false"><?php echo htmlspecialchars($license['web_client_support']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Протокол обмена DNP3</th><td contenteditable="false"><?php echo htmlspecialchars($license['exchange_protocol_dnp3']); ?></td></tr>
                        <tr class="str"><th data-copy="true">Уровень безопасности</th><td contenteditable="false"><?php echo htmlspecialchars($license['security_level']); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div></br>

        

        <!-- Уведомление с индикатором загрузки -->
        <div id="notification" class="alert alert-info" role="alert" style="display: none;">
            <div id="notificationContent">
                <span id="notificationMessage"></span>
                <div id="loadingSpinner" class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <button id="notificationClose" class="btn btn-link">Закрыть</button>
            </div>
        </div>

        <h2 class="mb-4">История лицензии № <?php echo htmlspecialchars($license['Номер_лицензии']); ?></h2>
        <?php include_once 'history.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <script src="./scripts/dataTables.js"></script>
    <script src="./scripts/main.js"></script>

    <!-- Передача данных из PHP в JavaScript через глобальные переменные -->
    <script>
        window.licenseData = {
            lastUpdateTime: <?php echo json_encode(isset($dateLatestUpdate) ? $dateLatestUpdate * 1000 : null); ?>,
            recID: <?php echo json_encode($license['Номер_лицензии']); ?>
        };
		$(document).ready(function() {
    // Функция для копирования текста в буфер обмена
    function copyToClipboard(text) {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }

    // Функция для отображения уведомления
    function showNotification(message) {
    var $notification = $('#notification');
    $notification.find('#notificationMessage').text(message);

    // Получаем размеры окна браузера и размеры уведомления
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var notificationWidth = $notification.outerWidth();
    var notificationHeight = $notification.outerHeight();

    // Рассчитываем позицию для центрирования уведомления
    var topPosition = (windowHeight - notificationHeight) / 2;
    var leftPosition = (windowWidth - notificationWidth) / 2;

    $notification.css({
        position: 'fixed',
        top: topPosition,
        left: leftPosition
    }).show();

    // Скрываем уведомление через 3 секунды
    setTimeout(function() {
        $notification.hide();
    }, 3000);
}

    // Инициализация tippy.js
    tippy('[data-copy="true"]', {
        content: 'Скопировано',
        trigger: 'click',
        onShow(instance) {
            setTimeout(() => {
                instance.hide();
            }, 1000);
        }
    });

    // Обработчик клика на ячейках первого столбца
    document.querySelectorAll('#licenseDetails th').forEach(th => {
        th.addEventListener('click', function() {
            const $row = $(this).closest('tr');
            const $rightCell = $row.find('td');
            const textToCopy = $rightCell.text();
            copyToClipboard(textToCopy);
            console.log('Скопировано из th: ' + textToCopy); // Добавляем логирование для отладки
        });
    });

    // Обработчик клика на ячейках второго столбца
    document.querySelectorAll('#licenseDetails td[data-copy="true"]').forEach(td => {
        td.addEventListener('click', function() {
            const textToCopy = this.innerText;
            copyToClipboard(textToCopy);
            console.log('Скопировано из td: ' + textToCopy); // Добавляем логирование для отладки
        });
    });

    // Проверка, что все элементы DOM загружены
    if ($('#licenseDetails').length) {
        console.log('Таблица загружена');
    } else {
        console.error('Таблица не найдена');
    }

    if ($('#notification').length) {
        console.log('Уведомление загружено');
    } else {
        console.error('Уведомление не найдено');
    }
});
        
    </script>
</body>

</html>