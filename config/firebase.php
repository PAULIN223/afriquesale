<?php

return [
    'default' => env('FIREBASE_PROJECT', 'app'),

    'projects' => [
        'app' => [
            'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
            'database'    => [
                'url' => env('FIREBASE_DATABASE_URL', ''),
            ],
            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_BUCKET', ''),
            ],
        ],
    ],
];
