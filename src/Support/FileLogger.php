<?php

declare(strict_types=1);

namespace TodoApi\Support;

use TodoApi\Http\Request;

final class FileLogger
{
    public function __construct(private readonly string $filePath)
    {
    }

    public function logRequest(Request $request, int $statusCode, float $durationMs): void
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $line = sprintf(
            "[%s] %s %s %d %.2fms ip=%s%s",
            date('Y-m-d H:i:s'),
            $request->getMethod(),
            $request->getPath(),
            $statusCode,
            $durationMs,
            $request->getClientIp(),
            PHP_EOL
        );

        file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
    }
}
