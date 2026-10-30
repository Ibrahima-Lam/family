<?php

return [
    'driver' => 'sqlite',
    'path' => getenv('DB_PATH') ?: dirname(__DIR__) . '/database/famille.db',
];
