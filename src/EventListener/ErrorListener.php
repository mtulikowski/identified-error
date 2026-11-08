<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\EventListener;

use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 64)]
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
