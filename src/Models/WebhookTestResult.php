<?php

declare(strict_types=1);

namespace TopTL\Models;

final class WebhookTestResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?int $statusCode,
        public readonly ?string $message,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: (bool) ($data['success'] ?? false),
            statusCode: isset($data['statusCode']) ? (int) $data['statusCode'] : (isset($data['status']) ? (int) $data['status'] : null),
            message: isset($data['message']) ? (string) $data['message'] : (isset($data['error']) ? (string) $data['error'] : null),
            raw: $data,
        );
    }
}
