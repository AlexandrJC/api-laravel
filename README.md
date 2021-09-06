1) Получение списка валют для отображения
/currencyNsi.php
Входные параметры - нет
Выходные значения: JSON 
Формат
{
    "header": {
        "id": 0,
        "name": 1
    },
    "data": [
        [
            "036",
            "Австралийский доллар"
        ],
        [
            "944",
            "Азербайджанский манат"
        ],
        [
            "826",
            "Фунт стерлингов Соединенного королевства"
        ]
    ]
}
Список валют загружается из https://www.cbr-xml-daily.ru/daily_json.js
ID вылюты - "NumCode"
Наименование валюты - "Name"

2) Получение списка проведенных расчетов
/list.php
Входные параметры - нет
Выходные значения: JSON
Формат
[
    {
        "id": 1,
        "from_currency": "036",
        "to_currency": "944",
        "amount": "10",
        "course": "1.22",
        "converted": "12.2",
        "date_added": "2021-02-04 14:24:17.343879"
    }
]

2) Добавление расчета
/add.php
Входные параметры - JSON

Формат
{
    "from_currency": "036",
    "to_currency": "944",
    "amount": "10",
}

Выходные значения: JSON
Формат ответа успешного сохранения
{
    "success":1,
    "id": 1,
    "from_currency": "036",
    "to_currency": "944",
    "amount": "10",
    "converted": "12.2",
    "date_added": "2021-02-04 14:24:17.343879"
}

Формат ответа при ошибке сохранения
{
    "success":0,
    "message": "Сообщение об ошибке"
}

Ошибку вызывает отрицательное значение суммы в запросе или отсутствие параметров запроса

3) Изменение и удаление расчета
/update.php
Входные параметры - JSON

Формат
{
    "deleted":
    [
        {
            "id":1
        },
        {
            "id":2
        }
    ],
    "updated":
    [
        {
            "id": 1,
            "from_currency": "036",
            "to_currency": "944",
            "amount": "10"
        },
        {
            "id": 1,
            "from_currency": "036",
            "to_currency": "944",
            "amount": "10"
        }
    ]
}

Выходные значения: JSON
Формат ответа успешного сохранения
{
    "success":1,
    "updated":
    [
        {
            "id": 1,
            "converted": "12.2"
        },
        {
            "id": 2,
            "converted": "12.2"
        }
    ]
}

Формат ответа при ошибке сохранения
{
    "success":0,
    "message": "Сообщение об ошибке"
}

Ошибку вызывает удаление ранее удаленной строки или пустой запрос

