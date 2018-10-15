<?php
// Application middleware
 
// e.g: $app->add(new \Slim\Csrf\Guard);
// Adding dependencies
 
use Tuupola\Middleware\HttpBasicAuthentication;
 
$container = $app->getContainer();

$container["jwt"] = function ($container) {
    return new StdClass;
};


 
$app->add(new Tuupola\Middleware\JwtAuthentication([
    //"path" => "/",
    "logger" => $container['logger'],
    "secret" => getenv('JWT_SECRET'),
    "rules" => [
        new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
            "path" => "/",
            "ignore" => ["/token", "/login","/register"]
        ]),
        new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
            "ignore" => ["OPTIONS"]
        ]),
    ],
    "before" => function ($request, $arguments) use ($container) {
        $container["jwt"] = $arguments["decoded"];
    },
    "error" => function ($request, $response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response->withJson($data);
    }
]));
 
$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/api/token",
    "users" => [
        "user" => "password"
    ]
]));
 
 
// $app->add(new Tuupola\Middleware\CorsMiddleware([
//     //"logger" => $container["logger"],
//     "credentials" => true,
//     //'preflightContinue'=> false,
//     "error" => function ($request, $response, $arguments) {
//         return new UnauthorizedResponse($arguments["message"], 401);
//     }
// ]));