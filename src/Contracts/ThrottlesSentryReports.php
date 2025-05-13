<?php

namespace Esign\SentryThrottling\Contracts;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Lottery;
use Throwable;

interface ThrottlesSentryReports
{
    public function throttleSentry(Throwable $exception): Lottery | Limit | null;
}
