<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Smartgroup\IdentifiedErrorBundle\Controller\ErrorController;
use Smartgroup\IdentifiedErrorBundle\DependencyInjection\IdentifiedErrorExtension;
use Smartgroup\IdentifiedErrorBundle\EventListener\ErrorListener;
use Smartgroup\IdentifiedErrorBundle\Logger\ErrorIdProcessor;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IdentifiedErrorExtensionTest extends TestCase
{
    public function testDisabledBundleDoesNotRegisterServicesOrIntegrations(): void
    {
        $container = $this->createContainer();
        $container->loadFromExtension('identified_error', ['enabled' => false]);
        $extension = new IdentifiedErrorExtension();

        $extension->load([['enabled' => false]], $container);
        $extension->prepend($container);

        self::assertFalse($container->hasDefinition(ErrorListener::class));
        self::assertFalse($container->hasDefinition(ErrorIdProcessor::class));
        self::assertFalse($container->hasDefinition(ErrorController::class));
        self::assertSame([], $container->getExtensionConfig('framework'));
        self::assertSame([], $container->getExtensionConfig('twig'));
    }

    public function testEnabledBundleRegistersServicesAndDefaultControllerAndTwigPath(): void
    {
        $container = $this->createContainer();
        $container->loadFromExtension('identified_error');
        $extension = new IdentifiedErrorExtension();

        $extension->load([[]], $container);
        $extension->prepend($container);

        self::assertSame(
            [['error_controller' => ErrorController::class.'::show']],
            $container->getExtensionConfig('framework'),
        );
        self::assertSame('kernel.event_listener', array_key_first($container->getDefinition(ErrorListener::class)->getTags()));
        self::assertSame('monolog.processor', array_key_first($container->getDefinition(ErrorIdProcessor::class)->getTags()));
        self::assertArrayHasKey('controller.service_arguments', $container->getDefinition(ErrorController::class)->getTags());
    }

    public function testApplicationErrorControllerIsPreserved(): void
    {
        $container = $this->createContainer();
        $container->loadFromExtension('identified_error');
        $container->loadFromExtension('framework', ['error_controller' => 'App\\Controller\\ErrorController::show']);

        (new IdentifiedErrorExtension())->prepend($container);

        self::assertSame(
            [['error_controller' => 'App\\Controller\\ErrorController::show']],
            $container->getExtensionConfig('framework'),
        );
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new IdentifiedErrorExtension());
        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new TwigExtension());

        return $container;
    }
}
