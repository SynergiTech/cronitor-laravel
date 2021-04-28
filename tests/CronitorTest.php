<?php

namespace SynergiTech\Cronitor\Laravel\Tests;

use SynergiTech\Cronitor\Client;
use SynergiTech\Cronitor\Laravel\Facades\Cronitor;

class CronitorTest extends TestCase
{
    public function testTelemetryEnabled(): void
    {
        config([
            'cronitor.telemetry.enabled' => false,
        ]);
        $this->assertFalse(Cronitor::telemetryEnabled());

        config([
            'cronitor.telemetry.enabled' => true,
        ]);
        $this->assertTrue(Cronitor::telemetryEnabled());

        config([
            'cronitor.telemetry.enabled' => null,
        ]);
        $this->assertFalse(Cronitor::telemetryEnabled());
    }

    public function testMonitorJobWithTelemetryDisabled(): void
    {
        config([
            'cronitor.telemetry.enabled' => false,
        ]);

        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('telemetry')
                ->never();
        });

        $jobWasExecuted = false;

        $actualReturnValue = Cronitor::monitorJob('', function () use (&$jobWasExecuted) {
            $jobWasExecuted = true;
            return 'expected-return-value';
        });

        $this->assertTrue($jobWasExecuted);
        $this->assertSame('expected-return-value', $actualReturnValue);
    }

    public function testMonitorJobWithTelemetryEnabled(): void
    {
        config([
            'cronitor.telemetry.enabled' => true,
        ]);

        $this->mock(Client::class, function ($mock) {
            $mock->shouldReceive('telemetry->monitor->job')
                ->once()
                ->andReturn('expected-return-value');
        });

        $actualReturnValue = Cronitor::monitorJob('', function () {
        });

        $this->assertSame('expected-return-value', $actualReturnValue);
    }
}
