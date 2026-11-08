<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Controller;

use Psr\Log\LoggerInterface;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

final class ErrorController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly ErrorContext $errorContext,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function show(\Throwable $exception): Response
    {
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
        $headers = $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : [];
        $identifier = $this->errorContext->getIdentifier();
        $template = '@IdentifiedError/Exception/error'.$status.'.html.twig';

        if (!$this->twig->getLoader()->exists($template)) {
            $template = '@IdentifiedError/Exception/error.html.twig';
        }

        try {
            return new Response($this->twig->render($template, [
                'status_code' => $status,
                'identifier' => $identifier,
            ]), $status, $headers);
        } catch (\Throwable $renderingException) {
            $this->logger->error('Failed to render the identified error page.', [
                'exception' => $renderingException,
                'error_id' => $identifier,
            ]);

            $response = new Response(
                'Request failed. Please contact the site administrator. Error identifier: '.($identifier ?? 'unavailable'),
                $status,
                $headers,
            );
            $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

            return $response;
        }
    }
}
