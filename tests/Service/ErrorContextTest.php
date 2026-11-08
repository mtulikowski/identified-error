<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ErrorContextTest extends TestCase
{
    public function testIdentifyCreatesTimestampedRandomIdentifier(): void
    {
        $request = new Request();
        $context = new ErrorContext(new RequestStack());

        $context->identify($request);
        $identifier = $request->attributes->get('_error_id');

        self::assertIsString($identifier);
        self::assertMatchesRegularExpression('/^\d{8}T\d{6}Z-[a-f0-9]{32}$/', $identifier);
    }

    public function testIdentifyKeepsExistingIdentifier(): void
    {
        $request = new Request();
        $context = new ErrorContext(new RequestStack());

        $context->identify($request);
        $identifier = $request->attributes->get('_error_id');
        $context->identify($request);

        self::assertSame($identifier, $request->attributes->get('_error_id'));
    }

    public function testIdentifyStoresIdentifierOnMainRequest(): void
    {
        $stack = new RequestStack();
        $mainRequest = new Request();
        $subRequest = new Request();
        $stack->push($mainRequest);
        $stack->push($subRequest);
        $context = new ErrorContext($stack);

        $context->identify($subRequest);

        self::assertNotNull($context->getIdentifier());
        self::assertArrayHasKey('_error_id', $mainRequest->attributes->all());
        self::assertArrayNotHasKey('_error_id', $subRequest->attributes->all());
    }
}
