# To-Do List REST API (тестовое задание Junior PHP)

REST API для управления задачами на чистом PHP 8 + MySQL.

## Что реализовано

- CRUD-эндпоинты для задач (`POST`, `GET`, `PUT`, `DELETE`)
- слоистая архитектура (`Controller`, `Service`, `Repository`, `Infrastructure`)
- валидация входных данных
- CORS и обработка `OPTIONS` preflight
- логирование запросов в файл
- контейнеризация через Docker
- unit-тесты на `PHPUnit`
- OpenAPI-спецификация (`openapi.yaml`)

## Требования для локального запуска

- PHP 8.1+
- MySQL
- Composer
- расширение `pdo_mysql`

## Настройки БД (переменные окружения)

- `DB_HOST` (по умолчанию: `127.0.0.1`)
- `DB_PORT` (по умолчанию: `3306`)
- `DB_DATABASE` (по умолчанию: `todo_api`)
- `DB_USERNAME` (по умолчанию: `root`)
- `DB_PASSWORD` (по умолчанию: пусто)

База данных и таблица `tasks` создаются автоматически при запуске.

## Запуск локально

```bash
composer install
composer serve
```

Базовый URL: `http://localhost:8000`

## Запуск в Docker

```bash
docker compose up --build
```

После запуска API доступно по адресу: `http://localhost:8000`

## Тесты

```bash
composer test
```

## Swagger / OpenAPI

- Спецификация находится в файле `openapi.yaml`
- Быстрый просмотр через Swagger Editor: https://editor.swagger.io/

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

## Логирование

- Логи пишутся в `storage/logs/app.log`
- Формат: дата, метод, путь, статус ответа, длительность запроса, IP

## Примеры запросов

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
