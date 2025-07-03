<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Allow CORS on these routes

    'allowed_methods' => ['*'], // Allow all HTTP methods (GET, POST, PUT, DELETE, etc.)

    'allowed_origins' => ['*'], // Allow all origins — use specific origins in production for security

    'allowed_origins_patterns' => [], // You can use regex patterns here instead of hardcoded domains

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [], // Optional: headers to expose to the client

    'max_age' => 0, // How long the response can be cached by the browser (0 = no cache)

    'supports_credentials' => true, // Allow cookies/auth headers (only works with specific origins, not '*')
];
