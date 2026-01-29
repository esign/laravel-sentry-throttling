# Throttle exceptions being sent to Sentry

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-sentry-throttling.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-sentry-throttling)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-sentry-throttling.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-sentry-throttling)
![GitHub Actions](https://github.com/esign/laravel-sentry-throttling/actions/workflows/main.yml/badge.svg)

Laravel includes a built-in mechanism for throttling exceptions, but it doesn’t support defining throttling rules for individual reportables.
This package adds the ability to throttle exceptions specifically before they are sent to Sentry.
It's especially useful if you want to continue logging all exceptions locally while avoiding repeated reports of the same exception being sent to Sentry.
While Sentry does offer its own sampling via the sample_rate setting, that approach is percentage-based and less granular than exception-specific throttling.

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-sentry-throttling
```

You must configure the `before_send` option in your `config/sentry.php` file so that Sentry uses the throttling logic.
Add the following to your Sentry config:

```php
// config/sentry.php
return [
    // ...existing config...
    'before_send' => [\Esign\SentryThrottling\SentryThrottling::class, 'beforeSend'],
];
```

## Usage

### Implementing throttling

#### For Laravel 11+ (slim skeleton)

For newer Laravel applications that don't ship with a default exception handler, create a dedicated class that implements the `ThrottlesSentryReports` interface:

```php
<?php

namespace App\Exceptions;

use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Lottery;
use Throwable;

class SentryThrottleHandler implements ThrottlesSentryReports
{
    public function throttleSentry(Throwable $exception): Lottery | Limit | null
    {
        return match (true) {
            $exception instanceof BroadcastException => Limit::perMinute(300),
            default => Limit::none(),
        };
    }
}
```

#### For Laravel 10 and earlier

If your application has a default exception handler (typically `App\Exceptions\Handler`), you can implement the `ThrottlesSentryReports` interface directly on it:

```php
use App\Exceptions\ApiMonitoringException;
use Illuminate\Broadcasting\BroadcastException;
use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Lottery;
use Throwable;

class Handler extends ExceptionHandler implements ThrottlesSentryReports
{
    public function throttleSentry(Throwable $exception): Lottery | Limit | null
    {
        return match (true) {
            $exception instanceof BroadcastException => Limit::perMinute(300),
            $exception instanceof ApiMonitoringException => Lottery::odds(1, 1000),
            default => Limit::none(),
        };
    }
}
```

### Binding the interface

You must bind your implementation of `ThrottlesSentryReports` in the Laravel container so the package can resolve it. This is typically done in a service provider, such as `App\Providers\AppServiceProvider`:

**For Laravel 11+ (using dedicated class):**

```php
use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use App\Exceptions\SentryThrottleHandler;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ThrottlesSentryReports::class, SentryThrottleHandler::class);
    }
}
```

**For Laravel 10 and earlier (using exception handler):**

```php
use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use App\Exceptions\Handler;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ThrottlesSentryReports::class, Handler::class);
    }
}
```

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
