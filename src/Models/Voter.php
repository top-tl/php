<?php

declare(strict_types=1);

namespace TopTL\Models;

final class Voter
{
    public function __construct(
        public readonly ?string $userId,
        public readonly ?string $firstName,
        public readonly ?string $username,
        public readonly ?string $votedAt,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: isset($data['userId']) ? (string) $data['userId'] : (isset($data['id']) ? (string) $data['id'] : null),
            firstName: isset($data['firstName']) ? (string) $data['firstName'] : null,
            username: isset($data['username']) ? (string) $data['username'] : null,
            votedAt: ($data['votedAt'] ?? $data['createdAt'] ?? null) === null
                ? null
                : (string) ($data['votedAt'] ?? $data['createdAt']),
            raw: $data,
        );
    }
}
