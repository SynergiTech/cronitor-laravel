<?php

namespace SynergiTech\Cronitor\Laravel\Middleware;

use SynergiTech\Cronitor\Laravel\Contracts\HasCronitorKey;
use SynergiTech\Cronitor\Laravel\Facades\Cronitor;

class DispatcherMiddleware
{
    /**
     * @param mixed $command
     * @param callable $next
     * @return mixed
     */
    public function handle($command, $next)
    {
        if (! $this->commandHasCronitorKey($command)) {
            return $next($command);
        }

        $continuation = null;
        Cronitor::monitorJob($command->getMonitorKey(), function () use (&$continuation, $next, $command) {
            $continuation = $next($command);
        });

        return $continuation;
    }

    /**
     * @param mixed $command
     */
    protected function commandHasCronitorKey($command): bool
    {
        return is_object($command) and $command instanceof HasCronitorKey;
    }
}
