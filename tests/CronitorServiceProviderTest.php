<?php

namespace SynergiTech\Cronitor\Laravel\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Debug\ExceptionHandler;
use SynergiTech\Cronitor\Client;

class CronitorServiceProviderTest extends TestCase
{
    public function testRegistersExceptionHandler(): void
    {
        config([
            'cronitor.telemetry.enabled' => true,
        ]);
        config([
            'cronitor.telemetry.report_exceptions' => true,
        ]);

        $container = [];
        $history = Middleware::history($container);

        $expectedException = new RequestException('test-error', new Request('GET', ''));
        $mock = new MockHandler([
            $expectedException,
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $guzzle = new GuzzleClient([
            'handler' => $handlerStack,
        ]);

        $this->mock(ExceptionHandler::class, function ($mock) use ($expectedException) {
            $mock->shouldReceive('report')
                ->once()
                ->with($expectedException);
        });

        $cronitor = $this->app->make(Client::class, [
            'httpClient' => $guzzle,
        ]);
        $monitor = $cronitor->telemetry()->monitor('test-monitor');

        $monitor->ok();
    }

    public function testRegistersExceptionHandlerWithUnresolvableHandler(): void
    {
        config([
            'cronitor.telemetry.enabled' => true,
        ]);
        config([
            'cronitor.telemetry.report_exceptions' => true,
        ]);

        $container = [];
        $history = Middleware::history($container);

        $expectedException = new RequestException('test-error', new Request('GET', ''));
        $mock = new MockHandler([
            $expectedException,
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $guzzle = new GuzzleClient([
            'handler' => $handlerStack,
        ]);

        $this->app->bind(ExceptionHandler::class, function () {
            return null;
        });

        $cronitor = $this->app->make(Client::class, [
            'httpClient' => $guzzle,
        ]);
        $monitor = $cronitor->telemetry()->monitor('test-monitor');

        $monitor->ok();

        // the expectation here is that we didn't throw any error due to the
        // inability to resolve an ExceptionHandler
        $this->assertTrue(true);
    }
}
