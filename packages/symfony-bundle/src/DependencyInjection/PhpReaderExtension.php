<?php

declare(strict_types=1);

namespace Vollbehr\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Bridge\Symfony\DependencyInjection
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Symfony dependency injection extension for configuring PHP Reader services.
 *
 * @author Sven Vollbehr
 */
final class PhpReaderExtension extends Extension
{
    /**
     * Loads and registers the PHP Reader services into the Symfony container.
     *
     * @param array<array-key, mixed> $configs  Bundle configuration values.
     * @param ContainerBuilder        $container Service container instance.
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('php_reader.default_file_mode', $config['default_file_mode']);

        $definition = (new Definition(FileReaderFactory::class))
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(true)
            ->setArgument('$defaultMode', $config['default_file_mode']);

        $container->setDefinition(FileReaderFactory::class, $definition);
        $container->setAlias('php_reader.file_reader_factory', FileReaderFactory::class)
            ->setPublic(true);
    }

    /**
     * Returns the service alias published by the bundle.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return 'php_reader';
    }

    /**
     * Builds the configuration tree for the bundle.
     *
     * @param array<array-key, mixed> $config     Bundle configuration values.
     * @param ContainerBuilder        $container  Service container instance.
     *
     * @return Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }
}
