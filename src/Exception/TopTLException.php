<?php

declare(strict_types=1);

namespace TopTL\Exception;

/**
 * Base class for every exception this SDK raises.
 *
 * The HTTP-class subclasses let callers catch a specific class of
 * failure (auth, not-found, rate-limit, bad-payload) without string-
 * matching on the message.
 */
class TopTLException extends \RuntimeException
{
    public int $status;
    public mixed $responseBody;

    public function __construct(string $message, int $status = 0, mixed $body = null)
    {
        parent::__construct($message, $status);
        $this->status = $status;
        $this->responseBody = $body;
    }

    public static function forStatus(int $status, string $message, mixed $body): self
    {
        return match (true) {
            $status === 401 || $status === 403 => new AuthenticationException($message, $status, $body),
            $status === 404 => new NotFoundException($message, $status, $body),
            $status === 429 => new RateLimitException($message, $status, $body),
            $status >= 400 && $status < 500 => new ValidationException($message, $status, $body),
            default => new self($message, $status, $body),
        };
    }
}
