<?php

namespace Esign\SentryThrottling\Tests\Support;

use Illuminate\Foundation\Exceptions\Handler;
use Sentry\Laravel\Integration;
use Throwable;

class BaseExceptionHandler extends Handler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }
}
