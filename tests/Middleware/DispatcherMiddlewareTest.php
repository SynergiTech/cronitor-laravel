<?php

namespace SynergiTech\Cronitor\Laravel\Tests\Middleware;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SynergiTech\Cronitor\Client;
use SynergiTech\Cronitor\Laravel\Contracts\HasCronitorKey;
use SynergiTech\Cronitor\Laravel\Tests\TestCase;
use SynergiTech\Cronitor\Telemetry\Monitor;
use SynergiTech\Cronitor\Telemetry\TelemetryService;

class DispatcherMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cronitor.telemetry.enabled' => true,
        ]);
        config([
            'cronitor.telemetry.report_exceptions' => false,
        ]);
    }

    public function testHandlesCommandWithoutCronitorKey(): void
    {
        $job = new class() implements ShouldQueue {
            use Dispatchable;
            use InteractsWithQueue;
            use Queueable;
            use SerializesModels;

            public $jobRan = false;

            public function handle()
            {
                $this->jobRan = true;
            }
        };

        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('telemetry')
                ->never();
        });

        $this->app->make(Dispatcher::class)->dispatchNow($job);

        $this->assertTrue($job->jobRan);
    }

    public function testHandlesCommandWithCronitorKey(): void
    {
        $job = new class() implements ShouldQueue, HasCronitorKey {
            use Dispatchable;
            use InteractsWithQueue;
            use Queueable;
            use SerializesModels;

            public $jobRan = false;

            public function handle()
            {
                $this->jobRan = true;
            }

            public function getMonitorKey(): string
            {
                return 'test-key';
            }
        };

        $isRunEventSent = false;

        $this->mock(Client::class, function ($mock) use (&$isRunEventSent) {
            $telemetryMock = \Mockery::mock(TelemetryService::class);
            $telemetryMock->shouldReceive('monitor')
                ->andReturnUsing(function ($monitorKey) use ($telemetryMock) {
                    return new Monitor($telemetryMock, $monitorKey);
                });
            $telemetryMock->shouldReceive('sendEvent')
                ->twice()
                ->withArgs(function ($monitorKey, $event) use (&$isRunEventSent) {
                    if ($monitorKey !== 'test-key') {
                        return false;
                    }

                    if ($isRunEventSent) {
                        return $event->getState() === 'complete';
                    }

                    $isRunEventSent = true;
                    return $event->getState() === 'run';
                });

            $mock->shouldReceive('telemetry')
                ->once()
                ->andReturn($telemetryMock);
        });

        $this->app->make(Dispatcher::class)->dispatchNow($job);

        $this->assertTrue($job->jobRan);
        $this->assertTrue($isRunEventSent);
    }

    public function testHandlesCommandWithCronitorKeyThatThrows(): void
    {
        $job = new class() implements ShouldQueue, HasCronitorKey {
            use Dispatchable;
            use InteractsWithQueue;
            use Queueable;
            use SerializesModels;

            public function handle()
            {
                throw new \RuntimeException();
            }

            public function getMonitorKey(): string
            {
                return 'test-key';
            }
        };

        $this->mock(Client::class, function ($mock) use (&$isRunEventSent) {
            $telemetryMock = \Mockery::mock(TelemetryService::class);
            $telemetryMock->shouldReceive('monitor')
                ->andReturnUsing(function ($monitorKey) use ($telemetryMock) {
                    return new Monitor($telemetryMock, $monitorKey);
                });
            $telemetryMock->shouldReceive('sendEvent')
                ->twice()
                ->withArgs(function ($monitorKey, $event) use (&$isRunEventSent) {
                    if ($monitorKey !== 'test-key') {
                        return false;
                    }

                    if ($isRunEventSent) {
                        return $event->getState() === 'fail';
                    }

                    $isRunEventSent = true;
                    return $event->getState() === 'run';
                });

            $mock->shouldReceive('telemetry')
                ->once()
                ->andReturn($telemetryMock);
        });

        $this->expectException(\RuntimeException::class);

        $this->app->make(Dispatcher::class)->dispatchNow($job);
    }
}
