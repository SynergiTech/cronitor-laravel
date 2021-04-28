<?php

namespace SynergiTech\Cronitor\Laravel;

use Illuminate\Contracts\Foundation\Application;
use SynergiTech\Cronitor\Client;

class Cronitor
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(Application $app, Client $client)
    {
        $this->app = $app;
        $this->client = $client;
    }

    public function telemetryEnabled(): bool
    {
        return $this->app->config['cronitor.telemetry.enabled'] ?? false;
    }

    /**
     * @return mixed
     */
    public function monitorJob(string $monitorKey, callable $job)
    {
        if (! $this->telemetryEnabled()) {
            return $job();
        }

        $monitor = $this->client->telemetry()->monitor($monitorKey);
        return $monitor->job($job);
    }
}
