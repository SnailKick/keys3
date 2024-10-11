document.addEventListener('DOMContentLoaded', function() {
    const yesButton = document.getElementById('yesButton');
    const noButton = document.getElementById('noButton');
    const alertMessageElement = document.getElementById('alert_message');
    const container = document.getElementById('container');

    if (!yesButton || !noButton || !alertMessageElement || !container) {
        console.error("Необходимые элементы кнопок или элемент для сообщений отсутствуют.");
        return;
    }

    // Создаем элемент для отображения вопроса
    const questionElement = document.createElement('p');
    questionElement.id = 'question';
    container.insertBefore(questionElement, container.firstChild);

    // Функция для проверки наличия лицензии
    function checkLicense(num_lic, record_link, callback) {
        fetch('http://локальный ip адрес/keys/check.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                num_lic: num_lic,
                record_link: record_link
            }).toString()
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            callback(data);
        })
        .catch(error => {
            console.error("Ошибка при проверке лицензии: ", error);
            alertMessageElement.innerText = "Ошибка при проверке лицензии.";
        });
    }

    // Функция для добавления лицензии
    function addLicense(num_lic, record_link) {
        fetch('http://локальный ip адрес/keys/check.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                num_lic: num_lic,
                record_link: record_link,
                action: 'add_record'
            }).toString()
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            alertMessageElement.innerText = data.message;

            if (data.id) {
                // Добавляем таймер на 1 секунду перед переходом
                setTimeout(function() {
                    window.location.href = 'http://локальный ip адрес/keys/view.php?id=' + data.id;
                }, 1000);
            } else {
                console.error("ID лицензии не найден в ответе сервера.");
            }
        })
        .catch(error => {
            console.error("Ошибка при отправке данных: ", error);
            alertMessageElement.innerText = "Ошибка при добавлении лицензии: " + num_lic;
        });
    }

    // Проверка наличия лицензии при открытии всплывающего окна
    chrome.storage.local.get(['selectedText', 'pageUrl'], function(data) {
        if (chrome.runtime.lastError) {
            console.error("Ошибка при получении данных из chrome.storage.local:", chrome.runtime.lastError);
            return;
        }

        const num_lic = data.selectedText;
        const currentUrl = data.pageUrl;

        if (!num_lic || !currentUrl) {
            console.error("Отсутствуют необходимые данные: selectedText или pageUrl");
            alertMessageElement.innerText = "Отсутствуют необходимые данные.";
            return;
        }

        if (num_lic.length === 16) {
            // Проверяем наличие лицензии
            checkLicense(num_lic, currentUrl, function(response) {
                if (response.status === 'exists') {
                    alertMessageElement.innerText = response.message;
                    if (response.message === 'Лицензия и путь к задаче существует в базе данных') {
                        questionElement.innerText = 'Хотите перейти к лицензии?';
                    } else if (response.message === 'Лицензия существует в базе данных, но такого пути к задаче нет.') {
                        questionElement.innerText = 'Хотите добавить путь к задаче?';
                    }
                } else if (response.status === 'not_exists') {
                    // Лицензия не существует, отображаем кнопки "Да" и "Нет"
                    yesButton.style.display = 'inline-block';
                    noButton.style.display = 'inline-block';
                    questionElement.innerText = 'Желаете добавить лицензию в базу данных?';
                }
            });
        } else {
            console.error("Выделенный текст не является корректной лицензией.");
            alertMessageElement.innerText = "Выделенный текст не является корректной лицензией.";
        }
    });

    yesButton.addEventListener('click', function() {
        chrome.storage.local.get(['selectedText', 'pageUrl'], function(data) {
            if (chrome.runtime.lastError) {
                console.error("Ошибка при получении данных из chrome.storage.local:", chrome.runtime.lastError);
                return;
            }

            const num_lic = data.selectedText;
            const currentUrl = data.pageUrl;

            if (!num_lic || !currentUrl) {
                console.error("Отсутствуют необходимые данные: selectedText или pageUrl");
                alertMessageElement.innerText = "Отсутствуют необходимые данные.";
                return;
            }

            if (num_lic.length === 16) {
                // Добавляем лицензию
                addLicense(num_lic, currentUrl);
            } else {
                console.error("Выделенный текст не является корректной лицензией.");
                alertMessageElement.innerText = "Выделенный текст не является корректной лицензией.";
            }
        });
    });

    noButton.addEventListener('click', function() {
        window.close();
    });
});