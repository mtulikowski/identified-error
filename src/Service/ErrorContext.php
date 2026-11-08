<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ErrorContext
{
    private const ATTRIBUTE = '_error_id';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function identify(Request $request): void
    {
        $request = $this->requestStack->getMainRequest() ?? $request;
        if (!$request->attributes->has(self::ATTRIBUTE)) {
            $request->attributes->set(self::ATTRIBUTE, gmdate('Ymd\\THis\\Z').'-'.bin2hex(random_bytes(16)));
        }
    }

    public function getIdentifier(): ?string
    {
        $identifier = $this->requestStack->getMainRequest()?->attributes->get(self::ATTRIBUTE);

        return is_string($identifier) ? $identifier : null;
    }
}
