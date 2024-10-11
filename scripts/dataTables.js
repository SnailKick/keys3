$(document).ready(function() {
    // Инициализация DataTable
    window.table = $('#licensesTable').DataTable({
        "paging": true,
        "searching": true,
        "info": true,
        "pageLength": 50,
        "lengthMenu": [10, 25, 50, 100],
        "order": [
            [0, 'asc'] // Сортировка по первому столбцу по возрастанию
        ],
        "language": {
            "search": "Поиск:",
            "lengthMenu": "Показать _MENU_ записей",
            "info": "Показано с _START_ до _END_ из _TOTAL_ записей",
            "infoEmpty": "Нет записей",
            "infoFiltered": "(отфильтровано из _MAX_ записей)",
            "paginate": {
                "first": "Первый",
                "last": "Последний",
                "next": "Следующий",
                "previous": "Предыдущий"
            }
        },
        "columnDefs": [
            { "orderable": false, "targets": 1 }, // Отключение сортировки для второго столбца
            {
                "targets": 1,
                "createdCell": function (td, cellData, rowData, row, col) {
                    // Добавляем атрибут data-order для кастомной сортировки
                    if (cellData === 'Действительна') {
                        $(td).attr('data-order', 0);
                    } else if (cellData === 'Аннулирована') {
                        $(td).attr('data-order', 1);
                    } else if (cellData === 'Не найдена') {
                        $(td).attr('data-order', 2);
                    }
                }
            }
        ]
    });

    // Создаем элемент уведомления
    var notification = $('<div id="search-notification" ">Лицензия содержит русские символы.</div>');
    $('#licensesTable_filter').append(notification);

    // Создаем кнопку "Очистить" и вставляем её справа от поля поиска DataTables
    var clearButton = $('<button id="clear-search-btn" class="btn btn-secondary ml-2">Очистить</button>');
    $('#licensesTable_filter').append(clearButton);

    // Кастомная функция для удаления пробелов из поиска
    $('#licensesTable_filter input').on('keyup', function() {
        var searchValue = $(this).val();
        // Удаление пробелов с начала и конца строки
        searchValue = searchValue.trim();
        // Установка значения в поле поиска
        window.table.search(searchValue).draw();

        // Проверка поискового запроса
        if (checkSearchQuery(searchValue)) {
            notification.show();
        } else {
            notification.hide();
        }
    });

    // Функция для проверки поискового запроса
    function checkSearchQuery(searchValue) {
        // Проверка, начинается ли поисковый запрос с числа
        if (/\d/.test(searchValue[0])) {
            // Проверка, содержит ли поисковый запрос русские символы
            if (/[а-яА-Я]/.test(searchValue)) {
                return true;
            }
        }
        return false;
    }

    // Обработчик для кнопки "Очистить"
    $('#clear-search-btn').on('click', function() {
        $('#licensesTable_filter input').val(''); // Очищаем поле поиска
        window.table.search('').draw(); // Очищаем фильтр DataTable
        notification.hide(); // Скрываем уведомление
    });

    // Восстановление состояния пагинации
    const savedPage = localStorage.getItem('currentPage');
    if (savedPage) {
        window.table.page(parseInt(savedPage)).draw(false);
    }

    // Сохранение состояния пагинации при переходе на другую страницу
    $('#licensesTable').on('page.dt', function() {
        const info = window.table.page.info();
        localStorage.setItem('currentPage', info.page);
    });

    // Восстановление значения выпадающего списка
    const savedFilterValue = localStorage.getItem('filterValue');
    if (savedFilterValue) {
        $('#filterDropdown').val(savedFilterValue);
        window.table.column(1).search(savedFilterValue).draw();
    }

    // Обработчик для выпадающего списка фильтрации
    $('#filterDropdown').on('change', function() {
        const selectedValue = $(this).val();
        window.table.column(1).search(selectedValue).draw();
        localStorage.setItem('filterValue', selectedValue);
    });
});