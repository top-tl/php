<?php

declare(strict_types=1);

namespace TopTL\Models;

final class GlobalStats
{
    public function __construct(
        public readonly int $total,
        public readonly int $channels,
        public readonly int $groups,
        public readonly int $bots,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total: (int) ($data['total'] ?? 0),
            channels: (int) ($data['channels'] ?? 0),
            groups: (int) ($data['groups'] ?? 0),
            bots: (int) ($data['bots'] ?? 0),
            raw: $data,
        );
    }
}
