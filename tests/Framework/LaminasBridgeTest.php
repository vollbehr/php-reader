<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Framework\Laminas;

use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Vollbehr\Bridge\Laminas\ConfigProvider;
use Vollbehr\Support\FileReaderFactory;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Tests\Framework\Laminas
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Validates the Laminas service manager integration for PHP Reader.
 *
 * @author Sven Vollbehr
 */
final class LaminasBridgeTest extends TestCase
{
    /**
     * Ensures the Laminas service manager resolves the file reader factory.
     *
     * @return void
     */
    public function testServiceManagerResolvesFileReaderFactory(): void
    {
        $config = (new ConfigProvider())();
        $config['php-reader']['default_file_mode'] = 'rb';

        $container = new ServiceManager($config['service_manager']);
        $container->setService('config', $config);

        $factory = $container->get(FileReaderFactory::class);

        self::assertInstanceOf(FileReaderFactory::class, $factory);

        $tempFile = tempnam(sys_get_temp_dir(), 'php-reader-');
        file_put_contents($tempFile, 'test');

        $reader = $factory->open($tempFile);
        self::assertSame(4, $reader->getSize());

        unlink($tempFile);
    }
}
