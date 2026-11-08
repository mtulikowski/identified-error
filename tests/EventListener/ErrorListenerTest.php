<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Smartgroup\IdentifiedErrorBundle\EventListener\ErrorListener;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ErrorListenerTest extends TestCase
{
    public function testIdentifiesRequestWhenExceptionIsHandled(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $requestStack->push($request);
        $context = new ErrorContext($requestStack);
        $listener = new ErrorListener($context);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, new \RuntimeException('Failure'));

        $listener($event);

        self::assertMatchesRegularExpression(
            '/^\d{8}T\d{6}Z-[a-f0-9]{32}$/',
            $context->getIdentifier() ?? '',
        );
    }
}
