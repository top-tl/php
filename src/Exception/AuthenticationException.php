<?php

declare(strict_types=1);

namespace TopTL\Exception;

/** Raised on HTTP 401 / 403 — bad API key or missing scope. */
class AuthenticationException extends TopTLException {}
