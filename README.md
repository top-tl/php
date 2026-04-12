# TOP.TL PHP SDK

[![Latest Stable Version](https://img.shields.io/packagist/v/top-tl/toptl.svg)](https://packagist.org/packages/top-tl/toptl)
[![PHP Version](https://img.shields.io/packagist/php-v/top-tl/toptl.svg)](https://packagist.org/packages/top-tl/toptl)
[![License](https://img.shields.io/packagist/l/top-tl/toptl.svg)](LICENSE)

Official PHP SDK for the [TOP.TL](https://top.tl) Telegram Directory API.

## Installation

```bash
composer require top-tl/toptl
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use TopTL\TopTL;

$client = new TopTL('your-api-token');

// Get listing info
$listing = $client->getListing('mybot');
echo $listing->title;
echo $listing->memberCount;

// Get votes
$votes = $client->getVotes('mybot');
echo $votes->votes;
echo $votes->monthlyVotes;

// Check if a user has voted
$voted = $client->hasVoted('mybot', 123456789);
echo $voted ? 'Voted' : 'Not voted';

// Post stats
$client->postStats('mybot', memberCount: 5000, groupCount: 120);

// Get global stats
$stats = $client->getStats();
echo $stats->totalListings;
```

## Autoposter

Automatically post stats at regular intervals:

```php
use TopTL\TopTL;
use TopTL\Autoposter;

$client = new TopTL('your-api-token');

$autoposter = new Autoposter(
    client: $client,
    username: 'mybot',
    statsCallback: function () {
        // Return your current stats
        return [
            'memberCount' => getMyMemberCount(),
            'groupCount' => getMyGroupCount(),
        ];
    },
    interval: 1800, // 30 minutes (default)
);

$autoposter->onPost(function (array $result) {
    echo "Stats posted successfully\n";
});

$autoposter->onError(function (\Throwable $e) {
    echo "Error posting stats: " . $e->getMessage() . "\n";
});

// Start the loop (blocking)
$autoposter->start();
```

For non-blocking usage, call `postOnce()` from a cron job or scheduled task instead:

```php
$autoposter->postOnce();
```

## API Reference

### `TopTL` Client

| Method | Description |
|--------|-------------|
| `getListing(string $username): Listing` | Get listing info |
| `getVotes(string $username): VotesResponse` | Get votes for a listing |
| `hasVoted(string $username, int\|string $userId): bool` | Check if a user voted |
| `postStats(string $username, ?int $memberCount, ?int $groupCount): array` | Post stats |
| `getStats(): Stats` | Get global TOP.TL stats |

## License

MIT - see [LICENSE](LICENSE) for details.
