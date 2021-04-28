<?php

return [
    'api_key' => '',

    'telemetry' => [
        'enabled' => app()->isProduction(),
        'report_exceptions' => app()->isProduction(),
    ],
];
