<?php

declare(strict_types=1);

namespace TodoApi\Exception;

use RuntimeException;

final class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        private readonly array $body,
        private readonly array $headers = []
    ) {
        parent::__construct($body['message'] ?? 'HTTP error', $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
