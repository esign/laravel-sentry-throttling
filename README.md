# Throttle exceptions being sent to Sentry.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-sentry-throttling.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-sentry-throttling)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-sentry-throttling.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-sentry-throttling)
![GitHub Actions](https://github.com/esign/laravel-sentry-throttling/actions/workflows/main.yml/badge.svg)

A short intro about the package.

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

### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
