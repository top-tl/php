<?php

declare(strict_types=1);

namespace TopTL;

class Autoposter
{
    private TopTL $client;
    private string $username;
    private int $interval;
    private bool $running = false;

    /** @var callable */
    private $statsCallback;

    /** @var callable|null */
    private $onError;

    /** @var callable|null */
    private $onPost;

    /**
     * @param TopTL    $client        The TOP.TL client instance.
     * @param string   $username      The listing username to post stats for.
     * @param callable $statsCallback A callback that returns an array with optional keys: memberCount, groupCount.
     * @param int      $interval      Interval in seconds between posts (default: 1800 = 30 minutes).
     */
    public function __construct(
        TopTL $client,
        string $username,
        callable $statsCallback,
        int $interval = 1800,
    ) {
        $this->client = $client;
        $this->username = $username;
        $this->statsCallback = $statsCallback;
        $this->interval = $interval;
    }

    /**
     * Set a callback to be called on successful stat posts.
     */
    public function onPost(callable $callback): self
    {
        $this->onPost = $callback;
        return $this;
    }

    /**
     * Set a callback to be called on errors.
     */
    public function onError(callable $callback): self
    {
        $this->onError = $callback;
        return $this;
    }

    /**
     * Start the autoposter loop. This blocks the current process.
     * Use pcntl_alarm if available, otherwise falls back to sleep().
     */
    public function start(): void
    {
        $this->running = true;

        while ($this->running) {
            $this->postOnce();
            sleep($this->interval);
        }
    }

    /**
     * Stop the autoposter.
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * Post stats once (useful for manual invocation or cron jobs).
     */
    public function postOnce(): void
    {
        try {
            $stats = ($this->statsCallback)();
            $memberCount = $stats['memberCount'] ?? null;
            $groupCount = $stats['groupCount'] ?? null;

            $result = $this->client->postStats($this->username, $memberCount, $groupCount);

            if ($this->onPost !== null) {
                ($this->onPost)($result);
            }
        } catch (\Throwable $e) {
            if ($this->onError !== null) {
                ($this->onError)($e);
            }
        }
    }
}
