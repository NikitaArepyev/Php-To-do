<?php

declare(strict_types=1);

namespace TodoApi\Controller;

use TodoApi\Http\Request;
use TodoApi\Service\TaskService;

final class TaskController
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    public function list(): array
    {
        return [
            'status' => 200,
            'body' => ['data' => $this->taskService->listTasks()],
        ];
    }

    public function get(int $taskId): array
    {
        return [
            'status' => 200,
            'body' => ['data' => $this->taskService->getTask($taskId)],
        ];
    }

    public function create(Request $request): array
    {
        $task = $this->taskService->createTask($request->getJsonBody());

        return [
            'status' => 201,
            'body' => ['data' => $task],
        ];
    }

    public function update(int $taskId, Request $request): array
    {
        $task = $this->taskService->updateTask($taskId, $request->getJsonBody());

        return [
            'status' => 200,
            'body' => ['data' => $task],
        ];
    }

    public function delete(int $taskId): array
    {
        $this->taskService->deleteTask($taskId);

        return [
            'status' => 200,
            'body' => ['message' => 'Task deleted'],
        ];
    }
}
