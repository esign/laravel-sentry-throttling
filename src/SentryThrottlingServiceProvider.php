<?php

namespace Esign\SentryThrottling;

use Illuminate\Support\ServiceProvider;

class SentryThrottlingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('sentry-throttling.php')], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'sentry-throttling');

        $this->app->singleton('sentry-throttling', function () {
            return new SentryThrottling;
        });
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/sentry-throttling.php';
    }
}
