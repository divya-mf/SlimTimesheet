<?php
/**
 * This file is responsible for initializing the app.
 * 
*/
require '../vendor/autoload.php';
$settings=require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);


//fetch all the dependencies

require __DIR__ . '/../src/dependencies.php';

// Register routes
require '../src/routes.php';