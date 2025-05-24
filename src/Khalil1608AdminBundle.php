<?php

namespace Khalil1608\AdminBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class Khalil1608AdminBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        foreach ($config as $key => $c) {
            $container->parameters()->set($key, $c);
        }
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
