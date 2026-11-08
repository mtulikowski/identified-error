<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Logger;

use Monolog\LogRecord;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;

final class ErrorIdProcessor
{
    public function __construct(private readonly ErrorContext $errorContext)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (null !== $identifier = $this->errorContext->getIdentifier()) {
            $record->extra['error_id'] = $identifier;
        }

        return $record;
    }
}
