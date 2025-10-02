<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Io;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\FileReader;

final class FileReaderTest extends TestCase
{
    public function testOpensLocalFiles(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'reader');
        file_put_contents($path, 'abc');

        try {
            $reader = new FileReader($path);
            self::assertSame('a', $reader->read(1));
        } finally {
            @unlink($path);
        }
    }

    public function testSupportsStreamWrappers(): void
    {
        $fd = fopen('php://temp', 'w+b');
        fwrite($fd, 'XY');
        rewind($fd);

        $reader = new FileReader($fd);

        self::assertSame('XY', $reader->read(2));
    }
}
