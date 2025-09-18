<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Framework\Laravel;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Vollbehr\Bridge\Laravel\PhpReaderServiceProvider;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Tests\Framework\Laravel
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Verifies the Laravel service provider wiring for PHP Reader.
 *
 * @author Sven Vollbehr
 */
final class LaravelServiceProviderTest extends TestCase
{
    /**
     * Ensures the service provider binds the file reader factory into the container.
     *
     * @return void
     */
    public function testServiceProviderBindsFactory(): void
    {
        $app = new StubApplication();
        $app->instance('config', new Repository([
            'php-reader' => [
                'default_file_mode' => 'rb',
            ],
        ]));

        $provider = new PhpReaderServiceProvider($app);
        $provider->register();
        $provider->boot();

        self::assertTrue($app->bound(FileReaderFactory::class));

        /** @var FileReaderFactory $factory */
        $factory = $app->make(FileReaderFactory::class);
        self::assertInstanceOf(FileReaderFactory::class, $factory);

        $tempFile = tempnam(sys_get_temp_dir(), 'php-reader-');
        file_put_contents($tempFile, 'laravel');

        $reader = $factory->open($tempFile);
        self::assertSame(7, $reader->getSize());

        unlink($tempFile);
    }
}

/**
 * Lightweight Laravel application stub used within the tests.
 */
final class StubApplication extends Container
{
    /**
     * Returns a fake config path location for publishing assertions.
     *
     * @param string $path Relative configuration file path.
     *
     * @return string
     */
    public function configPath(string $path = ''): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Indicates the stub is never executing in console mode.
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return false;
    }
}
