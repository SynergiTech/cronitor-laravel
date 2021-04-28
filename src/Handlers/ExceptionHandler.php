<?php

namespace SynergiTech\Cronitor\Laravel\Handlers;

use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;
use SynergiTech\Cronitor\Telemetry\ExceptionHandlerInterface;

class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var IlluminateExceptionHandler|null
     */
    private $handler;

    public function __construct(?IlluminateExceptionHandler $handler = null)
    {
        $this->handler = $handler;
    }

    public function report(\Throwable $e): void
    {
        if ($this->handler === null) {
            return;
        }

        // ExceptionHandler::report(\Exception $e)
        if (! $e instanceof \Exception) {
            // @codeCoverageIgnoreStart
            $e = new \ErrorException(
                $e->getMessage(),
                $e->getCode(),
                E_ERROR,
                $e->getFile(),
                $e->getLine()
            );
            // @codeCoverageIgnoreEnd
        }

        $this->handler->report($e);
    }
}
