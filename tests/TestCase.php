<?php

namespace Esign\SentryThrottling\Tests;

use Esign\SentryThrottling\SentryThrottlingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SentryThrottlingServiceProvider::class];
    }
} 