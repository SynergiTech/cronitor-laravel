<?php

namespace SynergiTech\Cronitor\Laravel\Contracts;

interface HasCronitorKey
{
    public function getMonitorKey(): string;
}
