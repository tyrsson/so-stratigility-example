<?php

declare(strict_types=1);

use App\Handler\AboutPageHandler;
use App\Handler\HomePageHandler;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\Handler\NotFoundHandler;
use Laminas\Stratigility\Middleware\PathMiddlewareDecorator;
use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use Laminas\Stratigility\MiddlewarePipe;

use function Laminas\Stratigility\middleware;

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

$app = new MiddlewarePipe();

// Landing page
$app->pipe(middleware(function ($req, $handler) {
    if (! in_array($req->getUri()->getPath(), ['/', ''], true)) {
        return $handler->handle($req);
    }
    return (new HomePageHandler())->handle($req);
}));

$app->pipe(
    new PathMiddlewareDecorator(
        '/about',
        new RequestHandlerMiddleware(
            new AboutPageHandler()
        )
    )
);

// 404 handler
$app->pipe(
    new RequestHandlerMiddleware(
        new NotFoundHandler(
            new ResponseFactory()
        )
    )
);

$server = new RequestHandlerRunner(
    $app,
    new SapiEmitter(),
    static function () {
        return ServerRequestFactory::fromGlobals();
    },
    static function (\Throwable $e) {
        $response = (new ResponseFactory())->createResponse(500);
        $response->getBody()->write(sprintf(
            'An error occurred: %s',
            $e->getMessage
        ));
        return $response;
    }
);

$server->run();