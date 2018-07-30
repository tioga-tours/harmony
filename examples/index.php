<?php
declare(strict_types=1);

include_once "../vendor/autoload.php";

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Harmony\Examples\Controller\GetBookAction;
use WoohooLabs\Harmony\Examples\Controller\UserController;
use WoohooLabs\Harmony\Harmony;
use WoohooLabs\Harmony\Middleware\DispatcherMiddleware;
use WoohooLabs\Harmony\Middleware\FastRouteMiddleware;
use WoohooLabs\Harmony\Middleware\HttpHandlerRunnerMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

// Initializing the request and the response objects
$request = ServerRequestFactory::fromGlobals();
$response = new Response();

// Initializing the router
$router = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute("GET", "/me", function (ServerRequestInterface $request, ResponseInterface $response) {
        $response->getBody()->write("I am me!");

        return $response;
    });

    $r->addRoute("GET", "/users/{id}", [UserController::class, "getUser"]);
    $r->addRoute("GET", "/books/{id}", GetBookAction::class);
});

// Stacking up middleware
$harmony = new Harmony(ServerRequestFactory::fromGlobals(), new Response());
$harmony
    ->addMiddleware(new HttpHandlerRunnerMiddleware(new SapiEmitter()))
    ->addMiddleware(new FastRouteMiddleware($router))
    ->addMiddleware(new DispatcherMiddleware());

// Run!
$harmony();
