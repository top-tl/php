<?php

declare(strict_types=1);

namespace TopTL\Models;

class Listing
{
    public function __construct(
        public readonly string $username,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $category,
        public readonly ?string $type,
        public readonly ?int $memberCount,
        public readonly ?int $votes,
        public readonly ?string $avatar,
        public readonly ?string $url,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: $data['username'] ?? '',
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            type: $data['type'] ?? null,
            memberCount: isset($data['memberCount']) ? (int) $data['memberCount'] : null,
            votes: isset($data['votes']) ? (int) $data['votes'] : null,
            avatar: $data['avatar'] ?? null,
            url: $data['url'] ?? null,
            raw: $data,
        );
    }
}
