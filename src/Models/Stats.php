<?php

declare(strict_types=1);

namespace TopTL\Models;

class Stats
{
    public function __construct(
        public readonly int $totalListings,
        public readonly int $totalVotes,
        public readonly int $totalUsers,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalListings: (int) ($data['totalListings'] ?? 0),
            totalVotes: (int) ($data['totalVotes'] ?? 0),
            totalUsers: (int) ($data['totalUsers'] ?? 0),
            raw: $data,
        );
    }
}
