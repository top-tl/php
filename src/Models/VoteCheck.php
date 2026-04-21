<?php

declare(strict_types=1);

namespace TopTL\Models;

final class VoteCheck
{
    public function __construct(
        public readonly bool $voted,
        public readonly ?string $votedAt,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            voted: (bool) ($data['voted'] ?? $data['hasVoted'] ?? false),
            votedAt: isset($data['votedAt']) ? (string) $data['votedAt'] : null,
            raw: $data,
        );
    }
}
