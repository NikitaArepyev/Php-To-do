<?php

declare(strict_types=1);

namespace TodoApi\Http;

final class Response
{
    public static function json(int $statusCode, array $body, array $headers = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
