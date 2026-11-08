<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Smartgroup\IdentifiedErrorBundle\Controller\ErrorController;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class ErrorControllerTest extends TestCase
{
    public function testRendersStatusAndIdentifierAndPreservesExceptionHeaders(): void
    {
        $loader = new ArrayLoader([
            '@IdentifiedError/Exception/error404.html.twig' => '{{ status_code }}:{{ identifier }}',
        ]);
        $twig = new Environment($loader);
        $requestStack = new RequestStack();
        $request = new Request();
        $requestStack->push($request);
        $context = new ErrorContext($requestStack);
        $context->identify($request);
        $identifier = $context->getIdentifier();
        $controller = new ErrorController($twig, $context, new NullLogger());
        $exception = new NotFoundHttpException('Missing', null, 404, ['X-Error' => 'not-found']);

        $response = $controller->show($exception);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame($identifier, substr($response->getContent(), 4));
        self::assertSame('not-found', $response->headers->get('X-Error'));
    }

    public function testFallsBackToPlainTextWhenTemplateRenderingFails(): void
    {
        $loader = new ArrayLoader([
            '@IdentifiedError/Exception/error.html.twig' => '{{ missing.value }}',
        ]);
        $context = new ErrorContext(new RequestStack());
        $controller = new ErrorController(new Environment($loader, ['strict_variables' => true]), $context, new NullLogger());

        $response = $controller->show(new \RuntimeException('Failure'));

        self::assertSame(500, $response->getStatusCode());
        self::assertSame('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertStringContainsString('Error identifier: unavailable', $response->getContent());
    }
}
