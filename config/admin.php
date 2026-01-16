<?php

return [
    'jwt' => [
        'secret' => env('ADMIN_JWT_SECRET'),
        'ttl' => (int) env('ADMIN_JWT_TTL', 86400),
        'algo' => env('ADMIN_JWT_ALGO', 'HS256'),
    ],
];
