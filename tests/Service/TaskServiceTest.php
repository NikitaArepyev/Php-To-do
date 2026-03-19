<?php

declare(strict_types=1);

namespace TodoApi\Tests\Service;

use PHPUnit\Framework\TestCase;
use TodoApi\Exception\HttpException;
use TodoApi\Repository\TaskRepositoryInterface;
use TodoApi\Service\TaskService;

final class TaskServiceTest extends TestCase
{
    public function testCreateTaskDefaultsStatusToPending(): void
    {
        $repository = new InMemoryTaskRepository();
        $service = new TaskService($repository);

        $task = $service->createTask([
            'title' => '  Buy milk  ',
            'description' => '2L',
        ]);

        self::assertSame('Buy milk', $task['title']);
        self::assertSame('2L', $task['description']);
        self::assertSame('pending', $task['status']);
    }

    public function testCreateTaskRequiresTitle(): void
    {
        $repository = new InMemoryTaskRepository();
        $service = new TaskService($repository);

        try {
            $service->createTask(['description' => 'No title']);
            self::fail('Expected HttpException was not thrown.');
        } catch (HttpException $exception) {
            self::assertSame(422, $exception->getStatusCode());
            self::assertSame('Validation failed', $exception->getBody()['message']);
        }
    }

    public function testUpdateTaskPartiallyKeepsExistingFields(): void
    {
        $repository = new InMemoryTaskRepository();
        $service = new TaskService($repository);

        $created = $service->createTask([
            'title' => 'Initial title',
            'description' => 'Initial description',
            'status' => 'pending',
        ]);

        $updated = $service->updateTask((int)$created['id'], ['status' => 'done']);

        self::assertSame('Initial title', $updated['title']);
        self::assertSame('Initial description', $updated['description']);
        self::assertSame('done', $updated['status']);
    }

    public function testDeleteMissingTaskReturnsNotFound(): void
    {
        $repository = new InMemoryTaskRepository();
        $service = new TaskService($repository);

        try {
            $service->deleteTask(999);
            self::fail('Expected HttpException was not thrown.');
        } catch (HttpException $exception) {
            self::assertSame(404, $exception->getStatusCode());
            self::assertSame('Task not found', $exception->getBody()['message']);
        }
    }
}

final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    private array $tasks = [];
    private int $nextId = 1;

    public function listAll(): array
    {
        return array_values(array_reverse($this->tasks));
    }

    public function findById(int $taskId): ?array
    {
        return $this->tasks[$taskId] ?? null;
    }

    public function create(string $title, string $description, string $status): int
    {
        $id = $this->nextId++;
        $now = date('Y-m-d H:i:s');

        $this->tasks[$id] = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return $id;
    }

    public function updateById(int $taskId, string $title, string $description, string $status): void
    {
        if (!isset($this->tasks[$taskId])) {
            return;
        }

        $this->tasks[$taskId]['title'] = $title;
        $this->tasks[$taskId]['description'] = $description;
        $this->tasks[$taskId]['status'] = $status;
        $this->tasks[$taskId]['updated_at'] = date('Y-m-d H:i:s');
    }

    public function deleteById(int $taskId): bool
    {
        if (!isset($this->tasks[$taskId])) {
            return false;
        }

        unset($this->tasks[$taskId]);
        return true;
    }
}
