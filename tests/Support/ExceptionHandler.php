<?php

namespace Esign\SentryThrottling\Tests\Support;

use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Lottery;
use Sentry\Laravel\Integration;
use Throwable;

class ExceptionHandler extends Handler implements ThrottlesSentryReports
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    public function throttleSentry(Throwable $exception): Limit | Lottery | null
    {
        return match (true) {
            $exception instanceof MyException => Limit::perMinute(1),
            default => Limit::none(),
        };
    }
}
