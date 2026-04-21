<?php

declare(strict_types=1);

namespace TopTL\Exception;

/** Raised on HTTP 429 — retry after a backoff. */
class RateLimitException extends TopTLException {}
