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
    $logger = new Monolog\Logger('slimprojecct');
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/../logs/app.log', '\Monolog\Logger::DEBUG'));
    return $logger;
};

$container['Controller'] = function ($container)
{
	return new \Src\Controllers\Controller($container);
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
	return new \Src\Api\FileMakerWrapper($container);
};

//object of UserActivitiesController class
$container['Constants'] = function ($container)
{
	return new \Src\Api\Constants($container);
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

//object of database connectivity
$container['db'] = function ($container) {
    require_once (__DIR__ .'/api/FileMaker.php');

     define('FM_HOST', '172.16.9.42');
     define('FM_FILE', 'userActivities.fmp12');
     define('FM_USER', 'admin');
     define('FM_PASS', 'mindfire');
    
    //to create the FileMaker Object
    $fm = new FileMaker(FM_FILE, FM_HOST, FM_USER, FM_PASS);
    
    return $fm;
};