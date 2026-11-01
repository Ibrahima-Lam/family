<?php

return [
    'session_name' => getenv('SESSION_NAME') ?: 'famille_session',
    'session_lifetime' => (int) (getenv('SESSION_LIFETIME') ?: 7200),
    'session_same_site' => getenv('SESSION_SAME_SITE') ?: 'Lax',

    'csrf_field' => '_token',

    'login_max_attempts' => (int) (getenv('LOGIN_MAX_ATTEMPTS') ?: 5),
    'login_decay' => (int) (getenv('LOGIN_DECAY') ?: 900),

    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'",
    ],
];
