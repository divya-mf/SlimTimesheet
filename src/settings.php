<?php
//setting the .env file
$dotenv = new Dotenv\Dotenv(__DIR__.'/..');
$dotenv->load();
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
            "FM_HOST" => getenv('FM_HOST'),
            "FM_FILE" => getenv('FM_FILE'),
            "FM_USER" => getenv('FM_USER'),
            "FM_PASS" => getenv('FM_PASS')
        ],
    ],
];
