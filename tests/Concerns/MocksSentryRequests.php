<?php

namespace Esign\SentryThrottling\Tests\Concerns;

use Illuminate\Foundation\Application;
use Mockery;
use Mockery\MockInterface;
use Sentry\Transport\TransportInterface;

trait MocksSentryRequests
{
    protected MockInterface $sentry;

    protected function withSentryTransportMocking(Application $app): void
    {
        $this->sentry = Mockery::mock(TransportInterface::class);
        $app['config']->set('sentry.transport', $this->sentry);
    }

    protected function assertSentryRequestCount(int $count): void
    {
        $this->sentry->shouldHaveReceived('send')->times($count);
    }
}
