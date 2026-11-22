<?php

return [
    'database' => [
        'host' => getenv('SARAJEVO2B_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('SARAJEVO2B_DB_PORT') ?: '3306',
        'name' => getenv('SARAJEVO2B_DB_NAME') ?: 'sarajevo2b',
        'user' => getenv('SARAJEVO2B_DB_USER') ?: 'root',
        'password' => getenv('SARAJEVO2B_DB_PASSWORD') ?: '',
    ],
    'jwt' => [
        'secret' => getenv('SARAJEVO2B_JWT_SECRET') ?: 'CHANGE_ME',
        'ttl' => 3600,
    ],
    'external' => [
        'weather_api_key' => getenv('SARAJEVO2B_WEATHER_API_KEY') ?: '',
        'weather_base_url' => 'https://api.openweathermap.org/data/2.5/weather',
        'connect_timeout' => 3,
        'timeout' => 8,
    ],
    'cors' => [
        'allowed_origins' => array_filter(array_map('trim', explode(',', getenv('SARAJEVO2B_CORS_ORIGINS') ?: 'http://localhost,http://127.0.0.1'))),
    ],
];
