<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($container) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($container) { 
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['common'] = function ($container)
{
	return new \Src\Services\Common($container);
};

//object of AuthController class
$container['AuthController'] = function ($container)
{
	return new \Src\Controllers\AuthController($container);
};

//object of UserActivitiesController class
$container['UserActivitiesController'] = function ($container)
{ 
	return new \Src\Controllers\UserActivitiesController($container);
};

//object of UserActivitiesController class
$container['FileMakerWrapper'] = function ($container)
{
	return new \Src\Services\FileMakerWrapper($container);
};

//object of UserActivitiesController class
$container['Constants'] = function ($container)
{
	return new \Src\Services\Constants($container);
};

//object of UserActivitiesController class
$container['notFoundHandler'] = function ($container)
{ 
	$logger = $container->get('logger');
	return function ($request, $response) use ($logger)
	{ 
		$res=$response->withStatus(404);
		$logger->addInfo($res);
	 	$msg='404: PAGE NOT FOUND';
	 	return $msg;
	};
};

//405 error handler
$container['notAllowedHandler'] = function ($container)
{ 
	$logger = $container->get('logger');
	return function ($request, $response) use ($logger)
	{
		$res=$response->withStatus(405);
		$logger->addInfo($res);
		$msg='405: IMPROPER METHOD ASSIGNMENT';
		return $msg;
	};
};

//500 error handler
$container['phpErrorHandler'] = function ($container)
{ 
	$logger = $container->get('logger');
	return function ($request, $response) use ($logger)
	{
		$res=$response->withStatus(500);
		$logger->addInfo($res);
		$msg='500: Please try later';
		return $msg;
	};
}; 

// object for database connectivity
$container['db'] = function ($container) 
{
	$settings = $container->get('settings')['db'];

    require_once (__DIR__ .'/services/FileMaker.php');

     define('FM_HOST', $settings['FM_HOST']);
     define('FM_FILE', $settings['FM_FILE']);
     define('FM_USER', $settings['FM_USER']);
     define('FM_PASS', $settings['FM_PASS']);
    
    //to create the FileMaker Object
    $fm = new FileMaker(FM_FILE, FM_HOST, FM_USER, FM_PASS);
    
    return $fm;
};