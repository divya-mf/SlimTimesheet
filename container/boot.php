<?php
/**
 * This file is responsible for initializing the app.
 * 
*/
require '../vendor/autoload.php';

$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails'=> true,
	]
]);


//fetch all the dependencies
require __DIR__ . '/../src/dependencies.php';

// Register routes
require '../src/routes.php';