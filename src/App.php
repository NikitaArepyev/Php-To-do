<?php

declare(strict_types=1);

namespace TodoApi;

use Throwable;
use TodoApi\Controller\TaskController;
use TodoApi\Exception\HttpException;
use TodoApi\Http\Request;
use TodoApi\Http\Response;
use TodoApi\Support\FileLogger;

final class App
{
    private const CORS_HEADERS = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        'Access-Control-Max-Age' => '86400',
    ];

    public function __construct(
        private readonly TaskController $taskController,
        private readonly FileLogger $logger
    )
    {
    }

    public function run(): void
    {
        $request = Request::fromGlobals();
        $startedAt = microtime(true);
        $statusCode = 500;

        try {
            if ($request->getMethod() === 'OPTIONS') {
                $statusCode = 204;
                Response::noContent(204, self::CORS_HEADERS);
                return;
            }

            $result = $this->dispatch($request);
            $statusCode = $result['status'];
            $headers = array_merge(self::CORS_HEADERS, $result['headers'] ?? []);
            Response::json($statusCode, $result['body'], $headers);
            return;
        } catch (HttpException $exception) {
            $statusCode = $exception->getStatusCode();
            $headers = array_merge(self::CORS_HEADERS, $exception->getHeaders());
            Response::json($statusCode, $exception->getBody(), $headers);
            return;
        } catch (Throwable $exception) {
            $statusCode = 500;
            Response::json(500, ['message' => 'Internal Server Error', 'error' => $exception->getMessage()], self::CORS_HEADERS);
            return;
        } finally {
            $durationMs = (microtime(true) - $startedAt) * 1000;
            $this->logger->logRequest($request, $statusCode, $durationMs);
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
