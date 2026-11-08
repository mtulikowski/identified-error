<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Tests\Logger;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Smartgroup\IdentifiedErrorBundle\Logger\ErrorIdProcessor;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ErrorIdProcessorTest extends TestCase
{
    public function testAddsIdentifierToRecordExtra(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $requestStack->push($request);
        $context = new ErrorContext($requestStack);
        $context->identify($request);
        $identifier = $context->getIdentifier();
        $processor = new ErrorIdProcessor($context);
        $record = new LogRecord(new \DateTimeImmutable(), 'app', Level::Error, 'Failure');

        $processed = $processor($record);

        self::assertSame($identifier, $processed->extra['error_id']);
    }

    public function testDoesNotAddIdentifierWithoutRequestContext(): void
    {
        $processor = new ErrorIdProcessor(new ErrorContext(new RequestStack()));
        $record = new LogRecord(new \DateTimeImmutable(), 'app', Level::Error, 'Failure');

        $processed = $processor($record);

        self::assertArrayNotHasKey('error_id', $processed->extra);
    }
}
