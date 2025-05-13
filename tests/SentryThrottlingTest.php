<?php

namespace Esign\SentryThrottling\Tests;

use Esign\SentryThrottling\SentryThrottling;
use Esign\SentryThrottling\Tests\Support\MyException;
use Illuminate\Support\Facades\Config;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Sentry\Client;
use Sentry\ClientInterface;
use Sentry\State\HubInterface;

final class SentryThrottlingTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('sentry.dsn', 'https://publickey@sentry.dev/123');
        $app['config']->set('sentry.before_send', fn ($event, $hint) => app(SentryThrottling::class)->beforeSend($event, $hint));
        $app->bind(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Esign\SentryThrottling\Tests\Support\ExceptionHandler::class
        );
    }

    #[Test]
    public function it_throttles_sentry_reports(): void
    {
        $mock = $this->mock(HubInterface::class, function (MockInterface $mock) {
            return $mock->shouldReceive('captureException')->once();
        });
        

        $this->app->instance('sentry', $mock);

        report(new MyException());
    }
}