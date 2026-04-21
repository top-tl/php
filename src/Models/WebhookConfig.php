<?php

declare(strict_types=1);

namespace TopTL\Models;

final class WebhookConfig
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $rewardTitle,
        /** @var array<string,mixed> */
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? $data['webhookUrl'] ?? null,
            rewardTitle: $data['rewardTitle'] ?? null,
            raw: $data,
        );
    }
}
