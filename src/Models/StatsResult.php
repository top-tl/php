<?php

declare(strict_types=1);

namespace TopTL\Models;

final class StatsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $username,
        public readonly ?string $error,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        // `success` defaults to true when absent — the API uses its presence
        // (with false) to flag batch failures, and omits it on single-ok responses.
        return new self(
            success: array_key_exists('success', $data) ? (bool) $data['success'] : true,
            username: isset($data['username']) ? (string) $data['username'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
            raw: $data,
        );
    }
}
