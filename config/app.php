<?php

return [
    'name' => getenv('APP_NAME') ?: 'Famille Hamet Tenguella',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Dakar',
    'url' => rtrim(getenv('APP_URL') ?: '', '/'),
];
