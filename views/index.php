<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <title>Управление Лицензиями</title>
    <style>
        .card-header {
            background-color: #fff;
            color: #000;
            font-weight: bold;
        }
        .modal-header {
            background-color: #fff;
            color: #000;
        }
        .modal-footer {
            border-top: 1px solid #dee2e6;
        }
        .collapse {
            transition: height 0.35s ease;
        }
        .form-section {
            margin-bottom: 1rem;
        }
        .red {
            color: red;
        }
		#licensesTable th:nth-child(2),
		#licensesTable td:nth-child(2) {
			width: 50px;
			min-width: 50px;
			max-width: 50px;
		}
		#filterDropdown {
			margin: 20px;
		}
        /* Анимация загрузки */
        .loader {
            border: 16px solid #f3f3f3; /* Серый цвет */
            border-top: 16px solid #808080; /* Серый цвет */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
		#search-container {
            position: relative; /* Устанавливаем контейнер как позиционированный элемент */
            margin: 50px;
            font-family: Arial, sans-serif; /* Используем строгий шрифт */
        }

        #search-notification {
            position: absolute;
            top: -40px;
            background-color: #fff0f0; /* Темно-серый фон */
            color: #000000; /* Белый цвет текста */
            padding: 8px 12px;
            border-radius: 3px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3); /* Более глубокая тень */
            display: none;
            z-index: 10;
            font-size: 10px; /* Немного уменьшенный шрифт */
            letter-spacing: 0.5px; /* Незначительное межбуквенное пространство для строгого стиля */
        }

        #search-input {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ddd; /* Легкая граница вокруг поля поиска */
        }
    </style>
</head>
<body>
    <?php include_once './formatDateTime.php';?>
    <div class="container mt-4">
        <h1 class="mb-4">Управление Лицензиями</h1>

        

        <!-- Кнопки для раскрытия панелей -->
        <div class="mb-4">
            <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#checkLicensePanel" aria-expanded="false" aria-controls="checkLicensePanel">
                Проверка лицензии
            </button>
            <button class="btn btn-info ml-2" type="button" data-toggle="collapse" data-target="#addLicensePanel" aria-expanded="false" aria-controls="addLicensePanel">
                Добавить новую лицензию
            </button>
			<button class="btn btn-info ml-2" id="exportCsvBtn">Экспорт в CSV</button>
			<select id="filterDropdown" class="form-control ml-2" style="width: auto;">
				<option value="">Все</option>
				<option value="Действительна">Действительные</option>
				<option value="Аннулирована">Аннулированные</option>
				<option value="Не найдена">Не найденные</option>
			</select>
        </div>

        <!-- Панель проверки лицензии -->
        <div class="collapse" id="checkLicensePanel">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Проверка лицензии</h2>
                </div>
                <div class="card-body">
                    <div class="form-section">
                        <label for="licenseInput">Введите номер лицензии для проверки:</label>
                        <div class="input-group mb-3">
                            <input type="text" id="licenseInput" class="form-control" placeholder="Введите номер лицензии">
                            <div class="input-group-append">
                                <button id="checkLicenseBtn" class="btn btn-primary">Проверить</button>
                            </div>
                        </div>
                    </div>

                    <!-- Модальное окно с результатами проверки лицензии -->
                    <div class="modal fade" id="licenseModal" tabindex="-1" role="dialog" aria-labelledby="licenseModalLabel" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="licenseModalLabel">Результаты проверки лицензии</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body" id="licenseModalBody"></div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
									<button type="button" class="btn btn-primary" id="viewLicenseBtn" style="display: none;">Перейти</button>
									<button type="button" class="btn btn-primary" id="addPathBtn">Добавить лицензию в базу данных</button>
								</div>
							</div>
						</div>
					</div>

                    <!-- Модальное окно для ввода пути к задаче -->
                    <div class="modal fade" id="addPathModal" tabindex="-1" role="dialog" aria-labelledby="addPathModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addPathModalLabel">Добавить путь к задаче</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="taskPath">Путь к задаче:</label>
                                        <input type="text" id="taskPath" class="form-control" placeholder="Введите путь к задаче">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                    <button type="button" class="btn btn-primary" id="savePathBtn">Сохранить</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		
        <!-- Панель добавления новой лицензии -->
        <div class="collapse" id="addLicensePanel">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Добавить новую лицензию</h2>
                </div>
                <div class="card-body">
                    <form id="addLicenseForm">
                        <!-- Ваши поля формы -->
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label for="num_lic"><span class="red">*</span> Номер лицензии:</label>
                                <input type="text" id="num_lic" class="form-control" name="num_lic" required>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="record_link"><span class="red">*</span> Путь к задаче:</label>
                                <input type="text" id="record_link" class="form-control" name="record_link">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="name_count">Контрагент:</label>
                                <input type="text" id="name_count" class="form-control" name="name_count">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="the_end_user">Конечник:</label>
                                <input type="text" id="the_end_user" class="form-control" name="the_end_user">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="object">Объект:</label>
                                <input type="text" id="object" class="form-control" name="object">
                            </div>
                            <div class="form-group col-md-2">
                                <label for="record_name">Номер договора:</label>
                                <input type="text" id="record_name" class="form-control" name="record_name">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Добавить лицензию</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Анимация загрузки -->
        <div class="loader"></div>

        <div id="licenseTable" class="mt-4" style="display: none;">
            <!-- Таблица лицензий -->
            <?php if ($licenses->num_rows > 0): ?>
                <table id="licensesTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Состояние</th>
                            <th>Номер лицензии</th>
                            <th>Контрагент</th>
                            <th>Конечник</th>
                            <th>Объект</th>
                            <th>Телепараметры</th>
                            <th>Количество станций</th>
                            <th>Платформа</th>
                            <th>Дата создания</th>
                            <th>Дата разрешенного обновления</th>
                            <th>Дата обновления</th>
                            <th>Записи</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = $licenses->fetch_assoc()): ?>
                            <tr class="clickable-row" data-id="<?= htmlspecialchars($row['id']) ?>">
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['Состояние']) ?></td>
                                <td><?= htmlspecialchars($row['Номер лицензии']) ?></td>
                                <td><?= htmlspecialchars($row['Контрагент']) ?></td>
                                <td><?= htmlspecialchars($row['Конечник']) ?></td>
                                <td><?= htmlspecialchars($row['Объект']) ?></td>
                                <td><?= htmlspecialchars($row['volume_parameters']) ?></td>
                                <td><?= htmlspecialchars($row['users']) ?></td>
                                <td><?= htmlspecialchars($row['platform']) ?></td>
                                <td><?= formatDate($row['date_creation']) ?></td>
                                <td><?= formatDate($row['date_sub_upgrade']) ?></td>
                                <td><?= formatDate($row['date_latest_update']) ?></td>
                                <td><?= htmlspecialchars($row['records']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Нет данных для отображения.</p>
            <?php endif; ?>
        </div>

        <hr>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="./scripts/dataTables.js"></script>
    <script src="./scripts/main.js"></script>

    <script>
		// Показываем весь текст при наведении на строку
		$("td").hover(function() {
			$(this).prop('title', $.trim($(this).text()));
		});
		$("th").hover(function() {
			$(this).prop('title', $.trim($(this).text()));
		});
        // Показываем анимацию загрузки
        document.querySelector('.loader').style.display = 'block';

        // Добавляем задержку перед показом таблицы
        setTimeout(function() {
            document.querySelector('.loader').style.display = 'none';
            document.getElementById('licenseTable').style.display = 'block';
        }, 100);
    </script>
</body>
</html>
                                    
                                