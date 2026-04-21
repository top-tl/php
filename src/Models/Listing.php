<?php

declare(strict_types=1);

namespace TopTL\Models;

final class Listing
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $title,
        public readonly ?string $description,
        /** @var "CHANNEL"|"GROUP"|"BOT"|"" */
        public readonly string $type,
        public readonly int $memberCount,
        public readonly int $voteCount,
        /** @var string[] */
        public readonly array $languages,
        public readonly bool $verified,
        public readonly bool $featured,
        public readonly ?string $photoUrl,
        /** @var string[] */
        public readonly array $tags,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            username: (string) ($data['username'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            description: isset($data['description']) ? (string) $data['description'] : null,
            type: (string) ($data['type'] ?? ''),
            memberCount: (int) ($data['memberCount'] ?? 0),
            voteCount: (int) ($data['voteCount'] ?? 0),
            languages: array_values(array_map('strval', (array) ($data['languages'] ?? []))),
            verified: (bool) ($data['verified'] ?? false),
            featured: (bool) ($data['featured'] ?? false),
            photoUrl: isset($data['photoUrl']) ? (string) $data['photoUrl'] : null,
            tags: array_values(array_map('strval', (array) ($data['tags'] ?? []))),
            raw: $data,
        );
    }
}
