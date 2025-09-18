<?php

declare(strict_types=1);

namespace Vollbehr\Bridge\Laminas;

use Vollbehr\Bridge\Laminas\Service\FileReaderFactoryFactory;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Bridge\Laminas
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Provides Laminas service and dependency configuration for PHP Reader.
 *
 * @author Sven Vollbehr
 */
final class ConfigProvider
{
    /**
     * Returns the configuration array consumed by Laminas applications.
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'php-reader' => [
                'default_file_mode' => null,
            ],
            'service_manager' => $this->getServiceConfig(),
            'dependencies' => $this->getServiceConfig(),
        ];
    }

    /**
     * Builds the service manager configuration for the bridge.
     *
     * @return array<string, array<string, string>>
     */
    private function getServiceConfig(): array
    {
        return [
            'factories' => [
                FileReaderFactory::class => FileReaderFactoryFactory::class,
            ],
            'aliases' => [
                'php-reader.file_reader_factory' => FileReaderFactory::class,
            ],
        ];
    }
}
