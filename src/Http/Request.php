<?php

declare(strict_types=1);

namespace TodoApi\Http;

use TodoApi\Exception\HttpException;

final class Request
{
    private ?array $decodedJsonBody = null;

    private function __construct(
        private readonly array $server,
        private readonly string $rawBody
    ) {
    }

    public static function fromGlobals(): self
    {
        $rawBody = file_get_contents('php://input');
        return new self($_SERVER, $rawBody === false ? '' : $rawBody);
    }

    public function getMethod(): string
    {
        return strtoupper((string)($this->server['REQUEST_METHOD'] ?? 'GET'));
    }

    public function getPath(): string
    {
        $path = parse_url((string)($this->server['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $normalized = $path === null ? '/' : rtrim($path, '/');
        return $normalized === '' ? '/' : $normalized;
    }

    public function getClientIp(): string
    {
        return (string)($this->server['REMOTE_ADDR'] ?? '-');
    }

    public function getJsonBody(): array
    {
        if ($this->decodedJsonBody !== null) {
            return $this->decodedJsonBody;
        }

        if (trim($this->rawBody) === '') {
            $this->decodedJsonBody = [];
            return $this->decodedJsonBody;
        }

        $decoded = json_decode($this->rawBody, true);
        if (!is_array($decoded)) {
            throw new HttpException(400, ['message' => 'Invalid JSON payload']);
        }

        $this->decodedJsonBody = $decoded;
        return $this->decodedJsonBody;
    }
}
