# To-Do List REST API (тестовое задание Junior PHP)

Простое REST API для управления задачами, реализованное на чистом PHP 8 + MySQL.

## Требования

- PHP 8.1+
- MySQL сервер (локальный или удаленный)
- включено расширение `pdo_mysql`

## Настройки БД (переменные окружения)

- `DB_HOST` (по умолчанию: `127.0.0.1`)
- `DB_PORT` (по умолчанию: `3306`)
- `DB_DATABASE` (по умолчанию: `todo_api`)
- `DB_USERNAME` (по умолчанию: `root`)
- `DB_PASSWORD` (по умолчанию: пусто)

База данных и таблица `tasks` создаются автоматически при запуске.

## Запуск

```bash
php -S localhost:8000 router.php
```

Базовый URL: `http://localhost:8000`

## Эндпоинты

- `POST /tasks` - создать задачу
- `GET /tasks` - получить список задач
- `GET /tasks/{id}` - получить одну задачу
- `PUT /tasks/{id}` - обновить задачу
- `DELETE /tasks/{id}` - удалить задачу

## Поля запроса

- `title` (обязательно при создании, не должно быть пустым)
- `description` (необязательно)
- `status` (необязательно, одно из: `pending`, `in_progress`, `done`)

## Примеры

Создание задачи:

```bash
curl -X POST http://localhost:8000/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Купить молоко","description":"2 литра","status":"pending"}'
```

Получение всех задач:

```bash
curl http://localhost:8000/tasks
```

Обновление задачи:

```bash
curl -X PUT http://localhost:8000/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status":"done"}'
```

Удаление задачи:

```bash
curl -X DELETE http://localhost:8000/tasks/1
```
