<?php

declare(strict_types=1);

namespace TopTL\Models;

class VotesResponse
{
    public function __construct(
        public readonly int $votes,
        public readonly int $monthlyVotes,
        public readonly array $voters,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            votes: (int) ($data['votes'] ?? 0),
            monthlyVotes: (int) ($data['monthlyVotes'] ?? 0),
            voters: $data['voters'] ?? [],
            raw: $data,
        );
    }
}
