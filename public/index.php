<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$pdo = createPdoConnection();
ensureDatabaseSchema($pdo);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = $path === null ? '/' : rtrim($path, '/');
$path = $path === '' ? '/' : $path;

try {
    if ($method === 'GET' && $path === '/tasks') {
        listTasks($pdo);
    }

    if ($method === 'POST' && $path === '/tasks') {
        createTask($pdo);
    }

    if (preg_match('#^/tasks/(\d+)$#', $path, $matches) === 1) {
        $taskId = (int)$matches[1];

        if ($method === 'GET') {
            getTask($pdo, $taskId);
        }

        if ($method === 'PUT') {
            updateTask($pdo, $taskId);
        }

        if ($method === 'DELETE') {
            deleteTask($pdo, $taskId);
        }

        respond(405, ['message' => 'Method Not Allowed']);
    }

    respond(404, ['message' => 'Not Found']);
} catch (Throwable $exception) {
    respond(500, ['message' => 'Internal Server Error', 'error' => $exception->getMessage()]);
}

function listTasks(PDO $pdo): void
{
    $stmt = $pdo->query('SELECT id, title, description, status, created_at, updated_at FROM tasks ORDER BY id DESC');
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond(200, ['data' => $tasks]);
}

function getTask(PDO $pdo, int $taskId): void
{
    $stmt = $pdo->prepare('SELECT id, title, description, status, created_at, updated_at FROM tasks WHERE id = :id');
    $stmt->execute(['id' => $taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task === false) {
        respond(404, ['message' => 'Task not found']);
    }

    respond(200, ['data' => $task]);
}

function createTask(PDO $pdo): void
{
    $payload = readPayload();
    validateTaskPayload($payload, false);

    $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, description, status, created_at, updated_at)
         VALUES (:title, :description, :status, :created_at, :updated_at)'
    );

    $stmt->execute([
        'title' => trim((string)$payload['title']),
        'description' => isset($payload['description']) ? trim((string)$payload['description']) : '',
        'status' => normalizeStatus($payload['status'] ?? null),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $taskId = (int)$pdo->lastInsertId();
    getTask($pdo, $taskId);
}

function updateTask(PDO $pdo, int $taskId): void
{
    $existingTask = findTask($pdo, $taskId);
    if ($existingTask === null) {
        respond(404, ['message' => 'Task not found']);
    }

    $payload = readPayload();
    validateTaskPayload($payload, true);

    $title = array_key_exists('title', $payload) ? trim((string)$payload['title']) : $existingTask['title'];
    $description = array_key_exists('description', $payload) ? trim((string)$payload['description']) : $existingTask['description'];
    $status = array_key_exists('status', $payload) ? normalizeStatus($payload['status']) : $existingTask['status'];
    $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET title = :title, description = :description, status = :status, updated_at = :updated_at
         WHERE id = :id'
    );

    $stmt->execute([
        'title' => $title,
        'description' => $description,
        'status' => $status,
        'updated_at' => $now,
        'id' => $taskId,
    ]);

    getTask($pdo, $taskId);
}

function deleteTask(PDO $pdo, int $taskId): void
{
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
    $stmt->execute(['id' => $taskId]);

    if ($stmt->rowCount() === 0) {
        respond(404, ['message' => 'Task not found']);
    }

    respond(200, ['message' => 'Task deleted']);
}

function findTask(PDO $pdo, int $taskId): ?array
{
    $stmt = $pdo->prepare('SELECT id, title, description, status, created_at, updated_at FROM tasks WHERE id = :id');
    $stmt->execute(['id' => $taskId]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    return $task === false ? null : $task;
}

function readPayload(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        respond(400, ['message' => 'Invalid JSON payload']);
    }

    return $payload;
}

function validateTaskPayload(array $payload, bool $isPartial): void
{
    if (!$isPartial || array_key_exists('title', $payload)) {
        $title = isset($payload['title']) ? trim((string)$payload['title']) : '';
        if ($title === '') {
            respond(422, ['message' => 'Validation failed', 'errors' => ['title' => 'Title is required']]);
        }
    }

    if (array_key_exists('status', $payload)) {
        normalizeStatus($payload['status']);
    }
}

function normalizeStatus(mixed $status): string
{
    if ($status === null || trim((string)$status) === '') {
        return 'pending';
    }

    $value = strtolower(trim((string)$status));
    $allowed = ['pending', 'in_progress', 'done'];

    if (!in_array($value, $allowed, true)) {
        respond(422, ['message' => 'Validation failed', 'errors' => ['status' => 'Status must be one of: pending, in_progress, done']]);
    }

    return $value;
}

function respond(int $statusCode, array $body): void
{
    http_response_code($statusCode);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function createPdoConnection(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    $database = getenv('DB_DATABASE') ?: 'todo_api';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $rootDsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
    $rootPdo = new PDO($rootDsn, $username, $password, $options);
    $rootPdo->exec(sprintf('CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $database));

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
    return new PDO($dsn, $username, $password, $options);
}

function ensureDatabaseSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS tasks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            status ENUM("pending", "in_progress", "done") NOT NULL DEFAULT "pending",
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}
