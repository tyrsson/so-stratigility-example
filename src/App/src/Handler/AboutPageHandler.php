<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function ob_start;
use function ob_end_clean;
use function ob_get_clean;

final class AboutPageHandler implements RequestHandlerInterface
{
    private const TEMPLATE = __DIR__ . '/../../template/about.phtml';

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        ob_start();
        (require self::TEMPLATE)([
            'title' => 'About Page',
            'message' => 'About Us!'
        ]);
        $output = ob_get_clean();
        if ($output === false) {
            ob_end_clean();
            return new HtmlResponse('Error rendering template', 500);
        }
        return new HtmlResponse($output);
    }
}
