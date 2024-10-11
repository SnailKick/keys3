$(document).ready(function() {
    // Определяем начальные значения
    let typingTimer;
    const doneTypingInterval = 300;

    // Функция для загрузки таблицы
    function loadTable(query = '') {
        console.log('Загружаем таблицу с запросом:', query);
        // Реализация загрузки таблицы через AJAX или другой механизм
    }

    // Обработка поиска с задержкой
    $('#search').on('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            const query = $(this).val().trim();
            loadTable(query);
        }, doneTypingInterval);
    });

    // Обработка нажатия на кнопку проверки лицензии
    $('#search').on('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            const query = $(this).val().trim();
            loadTable(query);
        }, doneTypingInterval);
    });


    $('#licenseTable').on('click', '.save-btn', function() {
        const $row = $(this).closest('tr');
        const id = $row.data('id');
        const licenc_number = $row.find('input').eq(0).val();
        const actual = $row.find('input').eq(1).val();

        $.ajax({
            url: 'update_license.php',
            type: 'POST',
            data: { id: id, num_lic: licenc_number, actual: actual },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $row.find('input').prop('readonly', true);
                    $row.find('.edit-btn').show();
                    $row.find('.save-btn').hide();
                } else {
                    alert('Ошибка при обновлении: ' + response.message);
                }
            },
            error: function () {
                alert('Произошла ошибка при обновлении.');
            }
        });
    });
    
    // Обработка редактирования лицензий
    $('#licenseTable').on('click', '.edit-btn', function() {
        const $row = $(this).closest('tr');
        $row.find('input').prop('readonly', false);
        $row.find('.edit-btn').hide();
        $row.find('.save-btn').show();
    });

    $('#licenseTable').on('click', '.save-btn', function() {
        const $row = $(this).closest('tr');
        const id = $row.data('id');
        const licenc_number = $row.find('input').eq(0).val().trim();
        const license_status = $row.find('input').eq(1).val().trim();
        const latest_update = $row.find('input').eq(2).val().trim();

        $.ajax({
            url: 'update.php',
            method: 'POST',
            data: { id, licenc_number, license_status, latest_update },
            success: function(response) {
                alert(response);
                loadTable();
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX при сохранении изменений:', error);
            }
        });
    });

    // Удаление лицензий
    $('#licenseTable').on('click', '.delete-btn', function() {
        const id = $(this).closest('tr').data('id');

        $.ajax({
            url: 'delete.php',
            method: 'POST',
            data: { id },
            success: function(response) {
                alert(response);
                loadTable();
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX при удалении:', error);
            }
        });
    });

    // Инициализация DataTable и двойной клик для открытия страницы
    $('#licensesTable').DataTable();
    $('#licensesTable tbody').on('dblclick', 'tr.clickable-row', function() {
        const id = $(this).data('id');
        if (id) {
            window.location.href = 'view.php?id=' + id;
        } else {
            console.error('ID не найден');
        }
    });
	
	// Функция для загрузки данных лицензии
    function loadLicenseData(licenseId) {
        $.ajax({
            url: 'view.php', // PHP-скрипт для получения данных лицензии
            method: 'GET',
            data: { id: licenseId },
            beforeSend: function() {
                $('#notificationMessage').text('Загрузка данных...');
                $('#notification').show();
            },
            success: function(response) {
                $('#licenseDetailsContainer').html(response.licenseDetails);
                $('#licenseHistoryContainer').html(response.licenseHistory);
                $('#notificationMessage').text('Данные успешно загружены');
            },
            error: function() {
                $('#notificationMessage').text('Ошибка загрузки данных');
            },
            complete: function() {
                $('#loadingSpinner').hide();
            }
        });
    }
    // Переход на главную страницу по кнопке "Назад"
    $('#backBtn').click(function() {
        history.back('index.php');
    });

    // Ajax проверка лицензии с ограничением на частоту запросов
    // Определение функции showNotification
function showNotification(message) {
    $('#notificationMessage').html(message);
    $('#notification').show();
    $('#loadingSpinner').hide();
}

// AJAX проверка лицензии с ограничением на частоту запросов
const checkBtn = $('#checkBtn');
const checkCooldown = 86400000; // 24 часа в миллисекундах
const recID = window.licenseData?.recID || '';
let countdownInterval;

function startCountdown(remainingTime) {
    clearInterval(countdownInterval); // Очищаем предыдущий интервал, если он был
    countdownInterval = setInterval(function() {
        remainingTime -= 1000;
        if (remainingTime <= 0) {
            clearInterval(countdownInterval);
            checkBtn.text('Проверить');
            checkBtn.prop('disabled', false);
        } else {
            const hours = Math.floor((remainingTime % (24 * 60 * 60 * 1000)) / (60 * 60 * 1000));
            const minutes = Math.floor((remainingTime % (60 * 60 * 1000)) / (60 * 1000));
            const seconds = Math.floor((remainingTime % (60 * 1000)) / 1000);

            // Форматируем значения для отображения в двузначном формате
            const formattedHours = String(hours).padStart(2, '0');
            const formattedMinutes = String(minutes).padStart(2, '0');
            const formattedSeconds = String(seconds).padStart(2, '0');

            checkBtn.text(`Проверить (${formattedHours}:${formattedMinutes}:${formattedSeconds})`);
        }
    }, 1000);
}

checkBtn.on('click', function() {
    checkBtn.prop('disabled', true); // Отключаем кнопку, чтобы предотвратить повторные клики
    startCountdown(checkCooldown);   // Запускаем отсчёт
});


function checkButtonState() {
    const lastCheckTime = localStorage.getItem(`lastCheckTime_${recID}`);
    const currentTime = new Date().getTime();

    if (lastCheckTime && (currentTime - lastCheckTime < checkCooldown)) {
        const remainingTime = checkCooldown - (currentTime - lastCheckTime);
        startCountdown(remainingTime);
        checkBtn.prop('disabled', true);
    } else {
        checkBtn.prop('disabled', false);
        checkBtn.text('Проверить');
    }
}

checkButtonState();

checkBtn.click(function() {
    const currentTime = new Date().getTime();
    localStorage.setItem(`lastCheckTime_${recID}`, currentTime);
    checkBtn.prop('disabled', true);

    const remainingTime = checkCooldown;
    startCountdown(remainingTime);

    $('#notification').show();
    $('#loadingSpinner').show();
    $('#notificationMessage').text('Проверка лицензии в процессе...');

    $.ajax({
        url: 'lic_parsing_one.php',
        type: 'POST',
        data: { recID },
        success: function(response) {
            console.log('Успешный ответ сервера:', response);
            window.licenseData.lastUpdateTime = currentTime;
            showNotification(response);
        },
        error: function(xhr, status, error) {
            console.error('Ошибка проверки лицензии:', error);
            showNotification('Произошла ошибка при проверке лицензии.');
        },
        complete: function() {
            console.log('Завершение AJAX запроса');
            $('#loadingSpinner').hide();
        }
    });
});

    // Закрытие уведомления
    function bindNotificationCloseHandler() {
        $('#notificationClose').click(function() {
            $('#notification').hide();
            $('#loadingSpinner').hide();
            location.reload();
        });
    }
    bindNotificationCloseHandler();

    // Сохранение изменений
    $('#saveChangesBtn').click(function() {
    const data = getLicenseData();

    if (!data.num_lic) {
        alert('Номер лицензии не указан.');
        return;
    }

    $.ajax({
        url: 'save_license.php',
        method: 'POST',
        data,
        dataType: 'json',
        beforeSend: function() {
            showLoading('Сохранение данных...');
        },
        success: function(response) {
            $('#notificationMessage').text(response.message);
            if (response.status === 'success') {
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        },
        error: function(xhr, status, error) {
            console.error('Ошибка сохранения данных:', error);
            console.error('Response Text:', xhr.responseText);
            try {
                const responseJson = JSON.parse(xhr.responseText);
                $('#notificationMessage').text('Ошибка: ' + responseJson.message);
            } catch (e) {
                $('#notificationMessage').text('Ошибка при сохранении данных.');
            }
        },
        complete: function() {
            hideLoading();
        }
    });
});

    // Удаление лицензии
    $('#delete-btn').click(function() {
        const data = getLicenseData();
        if (!data.num_lic) {
            alert('Номер лицензии не указан.');
            return;
        }

        $.ajax({
            url: 'delete.php',
            method: 'POST',
            data,
            dataType: 'json',
            beforeSend: function() {
                showLoading('Обработка запроса...');
            },
            success: function(response) {
                $('#notificationMessage').text(response.message);
                if (response.status === 'success') {
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка при удалении:', error);
                $('#notificationMessage').text('Ошибка при удалении.');
            },
            complete: function() {
                hideLoading();
            }
        });
    });

    // Вспомогательные функции
    function getLicenseData() {
        return {
            num_lic: window.licenseData.recID,
            date_latest_update: getCellValueByHeader('Дата последнего обновления'),
            the_end_user: getCellValueByHeader('Конечник'),
            object: getCellValueByHeader('Объект'),
            record_name: getCellValueByHeader('Номер договора'),
            record_link: getCellValueByHeader('Путь к задаче'),
            name_count: getCellValueByHeader('Контрагент')
        };
    }

    function getCellValueByHeader(headerText) {
        const $row = $('#licenseDetails').find('tr').filter(function() {
            return $(this).find('th').text().trim() === headerText;
        });
        const cell = $row.find('td').eq(0);
        return cell.length ? cell.text().trim() : '';
    }

    function showLoading(message) {
        $('#notification').show();
        $('#loadingSpinner').show();
        $('#notificationMessage').text(message);
    }

    function hideLoading() {
        setTimeout(function() {
            $('#notification').hide();
            $('#loadingSpinner').hide();
        }, 2000);
        bindNotificationCloseHandler();
    }

    // Пример для дополнительных функций
    function addLicense() {
        alert('Функция добавления лицензии в базу данных');
    }

    function restoreLicense() {
        alert('Функция восстановления лицензии');
    }
});
$(document).ready(function () {
    function transliterate(text) {
        return text
            .replace(/[аА]/gi, 'A')
            .replace(/[вВ]/gi, 'B')
            .replace(/[сС]/gi, 'C')
            .replace(/[еЕ]/gi, 'E')
            .replace(/[нН]/gi, 'H')
            .replace(/a/g, 'A')
            .replace(/b/g, 'B')
            .replace(/c/g, 'C')
            .replace(/d/g, 'D')
            .replace(/e/g, 'E')
            .replace(/f/g, 'F')
            .replace(/g/g, 'G')
            .replace(/h/g, 'H');
    }

    function checkLicenseNumber(licenseNumber) {
        licenseNumber = transliterate(licenseNumber);
        if (licenseNumber.length < 16) {
            return { status: 'error', message: 'Номер лицензии слишком короткий. Должно быть 16 символов.' };
        } else if (licenseNumber.length > 16) {
            return { status: 'error', message: 'Номер лицензии слишком длинный. Должно быть 16 символов.' };
        } else if (licenseNumber[0] !== '0') {
            return { status: 'error', message: 'Номер лицензии должен начинаться с символа "0".' };
        }
        return { status: 'success', licenseNumber: licenseNumber };
    }

    function sendLicenseRequest(licenseNumber) {
        $.ajax({
            url: 'check_license.php',
            method: 'POST',
            data: { recID: licenseNumber },
            dataType: 'json',  // Ожидаем формат JSON в ответе
            success: function (response) {
                if (response.status === 'error') {
                    $('#licenseModalBody').html('<p>' + response.message + '</p>');
                } else {
                    let detailsHtml = '<h5>Детали лицензии:</h5><ul>';
                    if (response.details && response.details.length > 0) {
                        response.details.forEach(item => {
                            detailsHtml += '<li>' + item.name + ': ' + item.value + '</li>';
                        });
                    } else {
                        detailsHtml += '<li>Детали лицензии не найдены.</li>';
                    }
                    detailsHtml += '</ul>';
                    $('#licenseModalBody').html('Результат: ' + response.message + '<br>' + detailsHtml + '<br><h5>Лицензия есть в базе данных</h5>');

                    // Показываем кнопку "Перейти", если record имеет значение и actual != 3
                    if (response.id) {
                        if (response.actual != 3) {
                            $('#addPathBtn').hide();  // Скрываем кнопку "Добавить в базу данных"
                            $('#viewLicenseBtn').show().off('click').on('click', function () {
                                window.location.href = 'view.php?id=' + response.id; // Перенаправление на страницу с результатами
                            });
                        } else {
                            // Обновляем модальное окно с сообщением об удалении лицензии
                            $('#licenseModalBody').html('Результат: ' + response.message + '<br>' + detailsHtml + '<br><h5>Лицензия была удалена из базы данных</h5>');
                            $('#viewLicenseBtn').hide();  // Скрываем кнопку "Перейти"
                        }
                    } else {
                        // Если лицензии нет в базе данных
                        $('#licenseModalBody').html('Результат: ' + response.message + '<br>' + detailsHtml + '<br><h5>Лицензии нет в базе данных</h5>');
                        $('#viewLicenseBtn').hide();  // Скрываем кнопку "Перейти"
                        
                    }
                }
                $('#licenseModal').modal('show');
            },
            error: function (xhr, status, error) {
                console.error('Ошибка AJAX:', error);
                console.error('Response Text:', xhr.responseText);
                try {
                    const responseJson = JSON.parse(xhr.responseText);
                    alert('Ошибка: ' + responseJson.message);
                } catch (e) {
                    alert('Ошибка при выполнении запроса.');
                }
            }
        });
    }

    function checkLicense() {
        const licenseNumber = $('#licenseInput').val().trim();

        if (!licenseNumber) {
            alert('Введите номер лицензии!');
            return;
        }

        // Очищаем содержимое модального окна перед показом нового результата
        $('#licenseModalBody').html('');
        $('#viewLicenseBtn').hide(); // Скрыть кнопку "Перейти" перед запросом

        const checkResult = checkLicenseNumber(licenseNumber);
        if (checkResult.status === 'success') {
            sendLicenseRequest(checkResult.licenseNumber);
        } else {
            $('#licenseModalBody').html('<p>' + checkResult.message + '</p>');
            $('#licenseModal').modal('show');
            $('#addPathBtn').hide();  // Скрываем кнопку "Добавить в базу данных"
        }
    }

    // Обработчик нажатия на кнопку проверки лицензии
    $('#checkLicenseBtn').on('click', function () {
        checkLicense();
    });

    // Обработчик нажатия на кнопку добавления пути
    $('#addPathBtn').on('click', function () {
        $('#licenseModal').modal('hide');
        $('#addPathModal').modal('show');
    });

    // Обработчик сохранения пути лицензии
    $('#savePathBtn').on('click', function () {
        const licenseNumber = $('#licenseInput').val().trim();
        const taskPath = $('#taskPath').val().trim();

        if (!licenseNumber || !taskPath) {
            alert('Введите все необходимые данные!');
            return;
        }

        const checkResult = checkLicenseNumber(licenseNumber);
        if (checkResult.status === 'error') {
            alert(checkResult.message);
            return;
        }

        $.ajax({
            url: 'add_license.php',
            method: 'POST',
            data: { num_lic: transliterate(licenseNumber), record_link: taskPath },
            dataType: 'json',  // Ожидаем формат JSON в ответе
            success: function (response) {
                console.log('Save Path Response:', response);
                if (response.status === 'success') {
                    $('#addPathModal').modal('hide');
                    window.location.href = 'view.php?id=' + response.id;  // Перенаправление на страницу с результатами
                } else {
                    console.error('Server Error:', response.message);
                    alert(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);  // Логируем текст ответа сервера

                alert('Ошибка при отправке запроса. Пожалуйста, попробуйте еще раз.');
            }
        });
    });

    $('#addLicenseForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serializeArray();
        let data = {};
        formData.forEach(item => {
            if (item.name === 'num_lic') {
                data[item.name] = transliterate(item.value.trim());
            } else {
                data[item.name] = item.value.trim();
            }
        });

        const licenseNumber = data.num_lic;
        const checkResult = checkLicenseNumber(licenseNumber);
        if (checkResult.status === 'error') {
            alert(checkResult.message);
            return;
        }

        $.ajax({
            url: 'add_license.php',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                console.log('Add License Form Response:', response);
                if (response.status === 'success') {
                    // Очищаем поля формы после успешного добавления
                    $('#addLicenseForm')[0].reset();
                    window.location.href = 'view.php?id=' + response.id;
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ошибка AJAX:', error);
                alert('Ошибка при выполнении запроса.');
            }
        });
    });

    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        exportCsv();
    });

    function exportCsv() {
        $.ajax({
            url: 'export_csv.php',
            method: 'POST',
            xhrFields: {
                responseType: 'blob' // Указываем тип ответа как blob
            },
            success: function(data) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                a.href = url;
                a.download = 'exported_licenses.csv';
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            },
            error: function(xhr, status, error) {
                console.error("Ошибка при экспорте CSV: ", error);
            }
        });
    }
});
