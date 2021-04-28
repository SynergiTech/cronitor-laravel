<?php

namespace SynergiTech\Cronitor\Laravel;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SynergiTech\Cronitor\Client;
use SynergiTech\Cronitor\Laravel\Handlers\ExceptionHandler;
use SynergiTech\Cronitor\Laravel\Middleware\DispatcherMiddleware;

class CronitorServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/cronitor.php' => config_path('cronitor.php'),
            ], 'config');
        }

        $this->app->make(Dispatcher::class)->pipeThrough([DispatcherMiddleware::class]);
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cronitor.php', 'cronitor');

        $this->app->singleton(Client::class, function ($app, $params) {
            $apiKey = $this->app->config['cronitor.api_key'] ?? '';

            $client = new Client($apiKey, $params['httpClient'] ?? null);

            if ($this->app->config['cronitor.telemetry.report_exceptions'] ?? false) {
                $exceptionHandler = $this->app->make(IlluminateExceptionHandler::class);
                $client->telemetry()->withExceptionHandler(new ExceptionHandler($exceptionHandler));
            }

            return $client;
        });

        $this->app->bind(Cronitor::class, function ($app) {
            return new Cronitor($app, $app->make(Client::class));
        });
    }
}
