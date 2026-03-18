# API Demo Checklist

Короткий чеклист для быстрой демонстрации REST API на собеседовании.

## 1) Запуск сервера

```bash
php -S localhost:8000 router.php
```

## 2) Создать задачу

```bash
curl -X POST http://localhost:8000/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Подготовиться к собеседованию","description":"Повторить CRUD и валидацию","status":"pending"}'
```

Проверка: ответ `200`, в `data` есть `id`, `title`, `status`.

## 3) Получить список задач

```bash
curl http://localhost:8000/tasks
```

Проверка: ответ `200`, в `data` массив задач.

## 4) Получить задачу по id

```bash
curl http://localhost:8000/tasks/1
```

Проверка: ответ `200`, возвращается объект задачи.

## 5) Обновить задачу

```bash
curl -X PUT http://localhost:8000/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status":"done"}'
```

Проверка: ответ `200`, поле `status` изменилось на `done`.

## 6) Удалить задачу

```bash
curl -X DELETE http://localhost:8000/tasks/1
```

Проверка: ответ `200`, сообщение `Task deleted`.

## 7) Проверить валидацию

```bash
curl -X POST http://localhost:8000/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":""}'
```

Проверка: ответ `422`, ошибка по полю `title`.
