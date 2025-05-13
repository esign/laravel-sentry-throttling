<?php

namespace Esign\SentryThrottling\Facades;

use Illuminate\Support\Facades\Facade;

class SentryThrottlingFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sentry-throttling';
    }
}
