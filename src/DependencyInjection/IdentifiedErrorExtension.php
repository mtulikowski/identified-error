<?php

declare(strict_types=1);

namespace Smartgroup\IdentifiedErrorBundle\DependencyInjection;

use Smartgroup\IdentifiedErrorBundle\Controller\ErrorController;
use Smartgroup\IdentifiedErrorBundle\EventListener\ErrorListener;
use Smartgroup\IdentifiedErrorBundle\Logger\ErrorIdProcessor;
use Smartgroup\IdentifiedErrorBundle\Service\ErrorContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\DependencyInjection\Extension\PrependExtensionInterface;

final class IdentifiedErrorExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['enabled']) {
            return;
        }

        $container->register(ErrorContext::class)
            ->setAutowired(true)
            ->setPublic(false);

        $container->register(ErrorListener::class)
            ->setAutowired(true)
            ->addTag('kernel.event_listener', [
                'event' => 'kernel.exception',
                'method' => '__invoke',
                'priority' => 64,
            ]);

        $container->register(ErrorIdProcessor::class)
            ->setAutowired(true)
            ->addTag('monolog.processor');

        $container->register(ErrorController::class)
            ->setAutowired(true)
            ->setPublic(true)
            ->addTag('controller.service_arguments');
    }

    public function getAlias(): string
    {
        return 'identified_error';
    }

    public function prepend(ContainerBuilder $container): void
    {
        $frameworkConfigs = $container->getExtensionConfig('framework');
        $hasCustomController = false;
        foreach ($frameworkConfigs as $config) {
            if (array_key_exists('error_controller', $config)) {
                $hasCustomController = true;
            }
        }

        $bundleConfig = $this->processConfiguration(
            new Configuration(),
            $container->getExtensionConfig($this->getAlias()),
        );

        if ($bundleConfig['enabled'] && !$hasCustomController) {
            $container->prependExtensionConfig('framework', [
                'error_controller' => ErrorController::class.'::show',
            ]);
        }
    }
}
