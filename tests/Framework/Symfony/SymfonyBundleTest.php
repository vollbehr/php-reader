<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Framework\Symfony;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vollbehr\Bridge\Symfony\DependencyInjection\PhpReaderExtension;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Tests\Framework\Symfony
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Exercises the Symfony bundle integration for PHP Reader.
 *
 * @author Sven Vollbehr
 */
final class SymfonyBundleTest extends TestCase
{
    /**
     * Ensures the Symfony container exposes the file reader factory service.
     *
     * @return void
     */
    public function testBundleRegistersFactory(): void
    {
        $container = new ContainerBuilder();
        $extension = new PhpReaderExtension();

        $container->registerExtension($extension);
        $container->loadFromExtension($extension->getAlias(), [
            'default_file_mode' => 'rb',
        ]);

        $container->compile();

        $factory = $container->get(FileReaderFactory::class);

        self::assertInstanceOf(FileReaderFactory::class, $factory);

        $tempFile = tempnam(sys_get_temp_dir(), 'php-reader-');
        file_put_contents($tempFile, 'symfony');

        $reader = $factory->open($tempFile);
        self::assertSame(7, $reader->getSize());

        unlink($tempFile);
    }
}
