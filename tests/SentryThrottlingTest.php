<?php

namespace Esign\SentryThrottling\Tests;

use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use Esign\SentryThrottling\SentryThrottling;
use Esign\SentryThrottling\Tests\Concerns\MocksSentryRequests;
use Esign\SentryThrottling\Tests\Support\BaseExceptionHandler;
use Exception;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Lottery;
use PHPUnit\Framework\Attributes\Test;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Throwable;

final class SentryThrottlingTest extends TestCase
{
    use MocksSentryRequests;

    protected function registerThrottlesSentryReports(ThrottlesSentryReports $instance): void
    {
        $this->app->instance(ThrottlesSentryReports::class, $instance);
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sentry.dsn', 'https://publickey@sentry.dev/123');
        $app['config']->set('sentry.before_send', [SentryThrottling::class, 'beforeSend']);
        $app->bind(ExceptionHandler::class, BaseExceptionHandler::class);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_can_throttle_exceptions_using_a_limit(): void
    {
        // Arrange
        $this->registerThrottlesSentryReports(new class implements ThrottlesSentryReports {
            public function throttleSentry(Throwable $exception): Limit | Lottery | null
            {
                return Limit::perMinute(1);
            }
        });

        // Act
        report(new Exception());
        report(new Exception());

        // Assert
        $this->assertSentryRequestCount(1);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_can_throttle_exceptions_using_a_limit_using_a_key(): void
    {
        // Arrange
        $this->registerThrottlesSentryReports(new class implements ThrottlesSentryReports {
            public function throttleSentry(Throwable $exception): Limit | Lottery | null
            {
                return Limit::perMinute(1)->by($exception->getMessage());
            }
        });

        // Act
        report(new Exception('foo'));
        report(new Exception('foo'));
        report(new Exception('bar'));

        // Assert
        $this->assertSentryRequestCount(2);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_can_throttle_exceptions_using_a_lottery(): void
    {
        // Arrange
        Lottery::fix([true, false]);
        $this->registerThrottlesSentryReports(new class implements ThrottlesSentryReports {
            public function throttleSentry(Throwable $exception): Limit | Lottery | null
            {
                return Lottery::odds(1, 2);
            }
        });

        // Act
        report(new Exception());
        report(new Exception());

        // Assert
        $this->assertSentryRequestCount(1);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_wont_throttle_exceptions_using_unlimited(): void
    {
        // Arrange
        $this->registerThrottlesSentryReports(new class implements ThrottlesSentryReports {
            public function throttleSentry(Throwable $exception): Limit | Lottery | null
            {
                return Limit::none();
            }
        });

        // Act
        report(new Exception());
        report(new Exception());

        // Assert
        $this->assertSentryRequestCount(2);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_wont_throttle_exceptions_using_null(): void
    {
        // Arrange
        $this->registerThrottlesSentryReports(new class implements ThrottlesSentryReports {
            public function throttleSentry(Throwable $exception): Limit | Lottery | null
            {
                return null;
            }
        });

        // Act
        report(new Exception());
        report(new Exception());

        // Assert
        $this->assertSentryRequestCount(2);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_wont_throttle_exceptions_when_interface_was_not_bound_in_the_container(): void
    {
        // Arrange
        $this->app->instance(ThrottlesSentryReports::class, null);

        // Act
        report(new Exception());
        report(new Exception());

        // Assert
        $this->assertSentryRequestCount(2);
    }

    #[Test]
    #[DefineEnvironment('withSentryTransportMocking')]
    public function it_wont_throttle_exceptions_when_something_fails_during_the_rate_limiting_determination(): void
    {
        // Arrange
        $this->registerThrottlesSentryReports(new class implements ThrottlesSentryReports {
            public function throttleSentry(Throwable $exception): Limit | Lottery | null
            {
                throw new Exception("Something went wrong");
            }
        });

        // Act
        report(new Exception());
        report(new Exception());

        // Assert
        $this->assertSentryRequestCount(2);
    }
}