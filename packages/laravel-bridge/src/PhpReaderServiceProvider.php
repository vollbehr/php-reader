<?php

declare(strict_types=1);

namespace Vollbehr\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Bridge\Laravel
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Laravel service provider that registers PHP Reader services.
 *
 * @author Sven Vollbehr
 */
final class PhpReaderServiceProvider extends ServiceProvider
{
    /**
     * Registers the PHP Reader services with the Laravel application container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/php-reader.php', 'php-reader');

        $this->app->singleton(FileReaderFactory::class, function ($app) {
            $mode = $app['config']->get('php-reader.default_file_mode');

            return new FileReaderFactory($mode);
        });

        $this->app->alias(FileReaderFactory::class, 'php-reader.file_reader_factory');
    }

    /**
     * Publishes the PHP Reader configuration for consumers.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/php-reader.php' => $this->app->configPath('php-reader.php'),
        ], 'php-reader-config');
    }
}
