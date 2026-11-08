<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\EventListener;

use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ErrorListener
{
    public function __construct(private readonly ErrorContext $errorContext)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $this->errorContext->identify($event->getRequest());
    }
}
