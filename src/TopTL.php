<?php

declare(strict_types=1);

namespace TopTL;

use TopTL\Models\Listing;
use TopTL\Models\Stats;
use TopTL\Models\VotesResponse;

class TopTL
{
    private string $token;
    private string $baseUrl;

    public function __construct(string $token, string $baseUrl = 'https://top.tl/api/v1')
    {
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get listing info for a username.
     */
    public function getListing(string $username): Listing
    {
        $data = $this->request('GET', "/listing/{$username}");
        return Listing::fromArray($data);
    }

    /**
     * Get votes for a listing.
     */
    public function getVotes(string $username): VotesResponse
    {
        $data = $this->request('GET', "/listing/{$username}/votes");
        return VotesResponse::fromArray($data);
    }

    /**
     * Check if a user has voted for a listing.
     */
    public function hasVoted(string $username, int|string $userId): bool
    {
        $data = $this->request('GET', "/listing/{$username}/has-voted/{$userId}");
        return (bool) ($data['voted'] ?? false);
    }

    /**
     * Post stats for a listing (member count, group count, etc.).
     */
    public function postStats(string $username, ?int $memberCount = null, ?int $groupCount = null): array
    {
        $body = [];
        if ($memberCount !== null) {
            $body['memberCount'] = $memberCount;
        }
        if ($groupCount !== null) {
            $body['groupCount'] = $groupCount;
        }

        return $this->request('POST', "/listing/{$username}/stats", $body);
    }

    /**
     * Get global TOP.TL stats.
     */
    public function getStats(): Stats
    {
        $data = $this->request('GET', '/stats');
        return Stats::fromArray($data);
    }

    /**
     * Make an HTTP request to the API.
     *
     * @throws \RuntimeException on HTTP or cURL errors
     */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $url = $this->baseUrl . $path;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: toptl-php/1.0.0',
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException("cURL error: {$error}");
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException("API error (HTTP {$httpCode}): {$response}");
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode JSON response: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
