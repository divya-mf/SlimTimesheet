<?php
/**
 * middleware
 * Handles jwt authentication in headers through routes. 
 */
 
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
            "ignore" => ["/login","/register"]
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
 
// $app->add(function ($request, $response, $next) use ($container) {
//     $response = $next($request, $response);
//     $common = $container->get('common');
//     $newBody = $common->sanitize($request->getParsedBody());
//     $response->withBody($newBody);
//    // var_dump($response);exit;
//    return $response;
// });
$app->add(new Tuupola\Middleware\HttpBasicAuthentication([
    "path" => "/api/token",
    "users" => [
        "user" => "password"
    ]
]));
