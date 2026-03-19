<?php

declare(strict_types=1);

namespace TodoApi\Repository;

use DateTimeImmutable;
use PDO;

final class PdoTaskRepository implements TaskRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, title, description, status, created_at, updated_at FROM tasks ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $taskId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, title, description, status, created_at, updated_at FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        return $task === false ? null : $task;
    }

    public function create(string $title, string $description, string $status): int
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO tasks (title, description, status, created_at, updated_at)
             VALUES (:title, :description, :status, :created_at, :updated_at)'
        );

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function updateById(int $taskId, string $title, string $description, string $status): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
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
    }

    public function deleteById(int $taskId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute(['id' => $taskId]);

        return $stmt->rowCount() > 0;
    }
}
