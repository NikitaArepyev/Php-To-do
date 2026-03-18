# To-Do List REST API (Junior PHP Test)

Simple REST API for managing tasks, implemented on pure PHP 8 + MySQL.

## Requirements

- PHP 8.1+
- MySQL server (local or remote)
- `pdo_mysql` extension enabled

## DB config (environment variables)

- `DB_HOST` (default: `127.0.0.1`)
- `DB_PORT` (default: `3306`)
- `DB_DATABASE` (default: `todo_api`)
- `DB_USERNAME` (default: `root`)
- `DB_PASSWORD` (default: empty)

Database and `tasks` table are created automatically at startup.

## Run

```bash
php -S localhost:8000 router.php
```

Base URL: `http://localhost:8000`

## Endpoints

- `POST /tasks` - create task
- `GET /tasks` - list tasks
- `GET /tasks/{id}` - get one task
- `PUT /tasks/{id}` - update task
- `DELETE /tasks/{id}` - delete task

## Payload fields

- `title` (required for create, non-empty)
- `description` (optional)
- `status` (optional, one of: `pending`, `in_progress`, `done`)

## Examples

Create task:

```bash
curl -X POST http://localhost:8000/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Buy milk","description":"2 liters","status":"pending"}'
```

Get all tasks:

```bash
curl http://localhost:8000/tasks
```

Update task:

```bash
curl -X PUT http://localhost:8000/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status":"done"}'
```

Delete task:

```bash
curl -X DELETE http://localhost:8000/tasks/1
```
