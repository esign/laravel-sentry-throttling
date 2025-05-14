# Throttle exceptions being sent to Sentry.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-sentry-throttling.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-sentry-throttling)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-sentry-throttling.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-sentry-throttling)
![GitHub Actions](https://github.com/esign/laravel-sentry-throttling/actions/workflows/main.yml/badge.svg)

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-sentry-throttling
```

The package will automatically register a service provider.

Next up, you can publish the configuration file:
```bash
php artisan vendor:publish --provider="Esign\SentryThrottling\SentryThrottlingServiceProvider" --tag="config"
```

## Usage

### Implementing throttling

The recommended way to use this package is to implement the `ThrottlesSentryReports` interface on your application's default exception handler (typically `App\Exceptions\Handler`):

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

**Important:**  
You must bind your implementation of `ThrottlesSentryReports` in the Laravel container so the package can resolve it.  
This is typically done in a service provider, such as `App\Providers\AppServiceProvider`:

```php
use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use App\Exceptions\Handler;

public function register()
{
    $this->app->bind(ThrottlesSentryReports::class, Handler::class);
}
```

If you prefer, you may also bind any other class implementing `ThrottlesSentryReports`:

```php
use Esign\SentryThrottling\Contracts\ThrottlesSentryReports;
use App\Services\CustomSentryThrottler;

public function register()
{
    $this->app->bind(ThrottlesSentryReports::class, CustomSentryThrottler::class);
}
```

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
