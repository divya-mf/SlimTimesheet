<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings.
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings.
        'logger' => [
            'name' => 'slimproject',
            'path' =>  __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ], 

        // db credentials for connectivity.
        "db" => [
            "FM_HOST" => "172.16.9.42",
            "FM_FILE" => "userActivities.fmp12",
            "FM_USER" => "admin",
            "FM_PASS" => "mindfire"
        ],
    ],
];
