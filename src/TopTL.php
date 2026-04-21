<?php

declare(strict_types=1);

namespace TopTL;

use TopTL\Models\GlobalStats;
use TopTL\Models\Listing;
use TopTL\Models\StatsResult;
use TopTL\Models\VoteCheck;
use TopTL\Models\Voter;
use TopTL\Models\WebhookConfig;
use TopTL\Models\WebhookTestResult;

/**
 * Synchronous client for the TOP.TL public API.
 *
 *   $client = new \TopTL\TopTL('toptl_xxx');
 *   $listing = $client->getListing('durov');
 *   $client->postStats('mybot', memberCount: 5000, groupCount: 1200);
 */
final class TopTL
{
    private string $token;
    private string $baseUrl;
    private int $timeout;
    private string $userAgent;

    public function __construct(
        string $token,
        string $baseUrl = 'https://top.tl/api',
        int $timeout = 15,
        ?string $userAgent = null
    ) {
        if ($token === '') {
            throw new \InvalidArgumentException('API token is required');
        }
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->userAgent = 'toptl-php/0.1.0' . ($userAgent !== null ? ' ' . $userAgent : '');
    }

    // ---- Listings ------------------------------------------------------

    public function getListing(string $username): Listing
    {
        return Listing::fromArray($this->request('GET', "/v1/listing/{$username}"));
    }

    /**
     * @return Voter[]
     */
    public function getVotes(string $username, ?int $limit = null): array
    {
        $path = "/v1/listing/{$username}/votes";
        if ($limit !== null) {
            $path .= '?limit=' . (int) $limit;
        }
        $data = $this->request('GET', $path);
        $items = is_array($data) && isset($data[0]) ? $data : ($data['items'] ?? []);
        return array_map([Voter::class, 'fromArray'], $items);
    }

    public function hasVoted(string $username, int|string $userId): VoteCheck
    {
        $data = $this->request('GET', "/v1/listing/{$username}/has-voted/{$userId}");
        return VoteCheck::fromArray($data);
    }

    // ---- Stats ---------------------------------------------------------

    /**
     * Update counters on a listing you own. Only the named args you pass
     * are sent — nulls are dropped so the server leaves those counters
     * untouched.
     *
     * @param string[]|null $botServes
     */
    public function postStats(
        string $username,
        ?int $memberCount = null,
        ?int $groupCount = null,
        ?int $channelCount = null,
        ?array $botServes = null
    ): StatsResult {
        $body = [];
        if ($memberCount !== null) $body['memberCount'] = $memberCount;
        if ($groupCount !== null) $body['groupCount'] = $groupCount;
        if ($channelCount !== null) $body['channelCount'] = $channelCount;
        if ($botServes !== null) $body['botServes'] = array_values($botServes);
        if ($body === []) {
            throw new \InvalidArgumentException(
                'postStats requires at least one of: memberCount, groupCount, channelCount, botServes'
            );
        }
        return StatsResult::fromArray(
            $this->request('POST', "/v1/listing/{$username}/stats", $body)
        );
    }

    /**
     * Post stats for up to 25 listings in a single request.
     *
     * @param array<int, array{username: string, memberCount?: int, groupCount?: int, channelCount?: int, botServes?: string[]}> $items
     * @return StatsResult[]
     */
    public function batchPostStats(array $items): array
    {
        if ($items === []) return [];
        $rows = [];
        foreach ($items as $i) {
            $row = ['username' => $i['username']];
            foreach (['memberCount', 'groupCount', 'channelCount', 'botServes'] as $k) {
                if (array_key_exists($k, $i)) $row[$k] = $i[$k];
            }
            $rows[] = $row;
        }
        $data = $this->request('POST', '/v1/stats/batch', $rows);
        return array_map([StatsResult::class, 'fromArray'], is_array($data) ? $data : []);
    }

    public function getGlobalStats(): GlobalStats
    {
        return GlobalStats::fromArray($this->request('GET', '/v1/stats'));
    }

    // ---- Webhooks ------------------------------------------------------

    public function setWebhook(string $username, string $url, ?string $rewardTitle = null): WebhookConfig
    {
        $body = ['url' => $url];
        if ($rewardTitle !== null) $body['rewardTitle'] = $rewardTitle;
        return WebhookConfig::fromArray(
            $this->request('PUT', "/v1/listing/{$username}/webhook", $body)
        );
    }

    public function testWebhook(string $username): WebhookTestResult
    {
        return WebhookTestResult::fromArray(
            $this->request('POST', "/v1/listing/{$username}/webhook/test")
        );
    }

    // ---- Internal ------------------------------------------------------

    private function request(string $method, string $path, ?array $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: ' . $this->userAgent,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception\TopTLException("Transport error: {$err}", 0);
        }

        $decoded = $response === '' ? [] : json_decode($response, true);
        if ($response !== '' && json_last_error() !== JSON_ERROR_NONE) {
            $decoded = ['message' => $response];
        }

        if ($status >= 400) {
            $message = is_array($decoded) ? ($decoded['message'] ?? ($decoded['error'] ?? 'HTTP ' . $status)) : 'HTTP ' . $status;
            throw Exception\TopTLException::forStatus($status, (string) $message, $decoded ?? []);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
