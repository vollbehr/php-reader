<?php

declare(strict_types=1);

namespace Vollbehr\Bridge\Laminas\Service;

use Psr\Container\ContainerInterface;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Bridge\Laminas\Service
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Factory that creates the PHP Reader file reader factory for Laminas.
 *
 * @author Sven Vollbehr
 */
final class FileReaderFactoryFactory
{
    /**
     * Creates the file reader factory from Laminas configuration.
     *
     * @param ContainerInterface $container Service container instance.
     *
     * @return FileReaderFactory
     */
    public function __invoke(ContainerInterface $container): FileReaderFactory
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $mode = $config['php-reader']['default_file_mode'] ?? null;

        return new FileReaderFactory($mode);
    }
}
