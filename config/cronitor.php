<?php

return [
    /**
     * Provide your API key as listed at
     * https://cronitor.io/settings
     * If you only need to utilise the telemetry
     * API, you can provide just the "Send Events" key
     */
    'api_key' => '',

    'telemetry' => [
        /**
         * Whether to enable the telemetry functionality.
         * You may not want to submit telemetry events
         * from your dev/staging/CI environment
         */
        'enabled' => env('APP_ENV', 'production') === 'production',
        /**
         * Whether the client should report exceptions
         * to the ExceptionHandler rather than throwing them
         */
        'report_exceptions' => env('APP_ENV', 'production') === 'production',
    ],
];
