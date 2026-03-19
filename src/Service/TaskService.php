<?php

declare(strict_types=1);

namespace TodoApi\Service;

use TodoApi\Exception\HttpException;
use TodoApi\Repository\TaskRepositoryInterface;

final class TaskService
{
    public function __construct(private readonly TaskRepositoryInterface $taskRepository)
    {
    }

    public function listTasks(): array
    {
        return $this->taskRepository->listAll();
    }

    public function getTask(int $taskId): array
    {
        $task = $this->taskRepository->findById($taskId);
        if ($task === null) {
            throw new HttpException(404, ['message' => 'Task not found']);
        }

        return $task;
    }

    public function createTask(array $payload): array
    {
        $title = $this->normalizeTitle($payload, false, null);
        $description = $this->normalizeDescription($payload, '');
        $status = $this->normalizeStatus($payload, false, null);

        $taskId = $this->taskRepository->create($title, $description, $status);
        return $this->getTask($taskId);
    }

    public function updateTask(int $taskId, array $payload): array
    {
        $existingTask = $this->taskRepository->findById($taskId);
        if ($existingTask === null) {
            throw new HttpException(404, ['message' => 'Task not found']);
        }

        $title = $this->normalizeTitle($payload, true, (string)$existingTask['title']);
        $description = $this->normalizeDescription($payload, (string)$existingTask['description']);
        $status = $this->normalizeStatus($payload, true, (string)$existingTask['status']);

        $this->taskRepository->updateById($taskId, $title, $description, $status);

        return $this->getTask($taskId);
    }

    public function deleteTask(int $taskId): void
    {
        $isDeleted = $this->taskRepository->deleteById($taskId);
        if (!$isDeleted) {
            throw new HttpException(404, ['message' => 'Task not found']);
        }
    }

    private function normalizeTitle(array $payload, bool $isPartial, ?string $fallback): string
    {
        if (!$isPartial || array_key_exists('title', $payload)) {
            $title = trim((string)($payload['title'] ?? ''));
            if ($title === '') {
                throw new HttpException(422, ['message' => 'Validation failed', 'errors' => ['title' => 'Title is required']]);
            }

            return $title;
        }

        return $fallback ?? '';
    }

    private function normalizeDescription(array $payload, string $fallback): string
    {
        if (!array_key_exists('description', $payload)) {
            return $fallback;
        }

        return trim((string)$payload['description']);
    }

    private function normalizeStatus(array $payload, bool $isPartial, ?string $fallback): string
    {
        if (!array_key_exists('status', $payload)) {
            return $isPartial && $fallback !== null ? $fallback : 'pending';
        }

        $value = strtolower(trim((string)$payload['status']));
        if ($value === '') {
            return 'pending';
        }

        $allowed = ['pending', 'in_progress', 'done'];
        if (!in_array($value, $allowed, true)) {
            throw new HttpException(422, ['message' => 'Validation failed', 'errors' => ['status' => 'Status must be one of: pending, in_progress, done']]);
        }

        return $value;
    }
}
