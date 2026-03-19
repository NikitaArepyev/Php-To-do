<?php

declare(strict_types=1);

namespace TodoApi\Repository;

interface TaskRepositoryInterface
{
    public function listAll(): array;

    public function findById(int $taskId): ?array;

    public function create(string $title, string $description, string $status): int;

    public function updateById(int $taskId, string $title, string $description, string $status): void;

    public function deleteById(int $taskId): bool;
}
