# cronitor-laravel
[![Tests](https://github.com/SynergiTech/cronitor-laravel/actions/workflows/tests.yaml/badge.svg)](https://github.com/SynergiTech/cronitor-laravel/actions/workflows/tests.yaml)

## Install
```sh
composer require "synergitech/cronitor-laravel"
```

### Configuration
```sh
php artisan vendor:publish --provider="SynergiTech\Cronitor\Laravel\CronitorServiceProvider"
```

## Usage
### Automatically monitoring a Job
Your Jobs can be automatically monitored by this package by implementing the `HasCronitorKey` contract.
```php
use SynergiTech\Cronitor\Laravel\Contracts\HasCronitorKey;

class YourJob implements HasCronitorKey
{
    public function getMonitorKey(): string
    {
        return 'your monitor key from cronitor.io';
    }
}
```

When your Job is dispatched, a Dispatcher middleware will automatically send telemetry events based on whether your job is successful.

### Monitoring arbitrary code
Additionally, you can monitor any callback via the `Cronitor` facade:
```php
use SynergiTech\Cronitor\Laravel\Facades\Cronitor;

class YourClass
{
    public function handle()
    {
        Cronitor::monitorJob('your monitor key', function () {
            throw new \Exception('This will automatically be reported as a fail event');
        });
    }
}
```
