<?php

namespace Esign\SentryThrottling\Tests;

use Esign\SentryThrottling\SentryThrottlingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sentry\Laravel\ServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class, SentryThrottlingServiceProvider::class];
    }
} 