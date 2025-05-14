<?php

namespace Esign\SentryThrottling;

use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Support\Lottery;
use Sentry\Event;
use Sentry\EventHint;
use Throwable;

class SentryThrottling
{
    public function beforeSend(Event $event, ?EventHint $hint): ?Event
    {
        if (! $hint?->exception instanceof Throwable) {
            return $event;
        }

        $throttler = null;
        if (app()->bound(ThrottlesSentryReports::class)) {
            $throttler = app(ThrottlesSentryReports::class);
        }

        if (! $throttler) {
            return $event;
        }

        return rescue(fn () => with($throttler->throttleSentry($hint->exception), function ($throttle) use ($hint, $event) {
            if ($throttle instanceof Unlimited || $throttle === null) {
                return $event;
            }

            if ($throttle instanceof Lottery) {
                return $throttle($hint->exception) ? $event : null;
            }

            $rateLimiter = app(RateLimiter::class);

            return $rateLimiter->attempt(
                with($throttle->key ?: 'esign:laravel-sentry-throttling:'.$hint->exception::class, fn ($key) => hash('xxh128', $key)),
                $throttle->maxAttempts,
                fn () => true,
                $throttle->decaySeconds
            ) ? $event : null;
        }), rescue: false, report: false);
    }
}
