<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | X-Cart-Session must be exposed so the browser-side JS can read it from
    | fetch() responses in cross-origin requests (e.g. frontend on :3000 →
    | backend on :8000). Without this, the session token is invisible to JS
    | and every guest-cart mutation creates a fresh cart → 403 on item ops.
    |
    */
    'exposed_headers' => ['X-Cart-Session'],

    'max_age' => 0,

    'supports_credentials' => false,
];
