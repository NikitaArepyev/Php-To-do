<?php

declare(strict_types=1);

namespace TodoApi;

use Throwable;
use TodoApi\Controller\TaskController;
use TodoApi\Exception\HttpException;
use TodoApi\Http\Request;
use TodoApi\Http\Response;

final class App
{
    public function __construct(private readonly TaskController $taskController)
    {
    }

    public function run(): void
    {
        $request = Request::fromGlobals();

        try {
            $result = $this->dispatch($request);
            Response::json($result['status'], $result['body'], $result['headers'] ?? []);
            return;
        } catch (HttpException $exception) {
            Response::json($exception->getStatusCode(), $exception->getBody(), $exception->getHeaders());
            return;
        } catch (Throwable $exception) {
            Response::json(500, ['message' => 'Internal Server Error', 'error' => $exception->getMessage()]);
            return;
        }
    }

    private function dispatch(Request $request): array
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        if ($path === '/tasks') {
            return match ($method) {
                'GET' => $this->taskController->list(),
                'POST' => $this->taskController->create($request),
                default => throw new HttpException(405, ['message' => 'Method Not Allowed']),
            };
        }

        if (preg_match('#^/tasks/(\d+)$#', $path, $matches) === 1) {
            $taskId = (int)$matches[1];

            return match ($method) {
                'GET' => $this->taskController->get($taskId),
                'PUT' => $this->taskController->update($taskId, $request),
                'DELETE' => $this->taskController->delete($taskId),
                default => throw new HttpException(405, ['message' => 'Method Not Allowed']),
            };
        }

        throw new HttpException(404, ['message' => 'Not Found']);
    }
}
