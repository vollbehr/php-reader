<?php

declare(strict_types=1);

namespace Vollbehr\Bridge\Laminas;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Bridge\Laminas
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Laminas module definition that exposes PHP Reader services.
 *
 * @author Sven Vollbehr
 */
final class Module implements ConfigProviderInterface
{
    /**
     * Returns module configuration for Laminas applications.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return (new ConfigProvider())();
    }
}
