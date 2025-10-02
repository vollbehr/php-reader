<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Io;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\FileWriter;

final class FileWriterTest extends TestCase
{
    public function testWritesLocalFiles(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'writer');
        try {
            $writer = new FileWriter($path);
            $writer->write('test');
            $writer->flush();
            $writer->close();

            self::assertSame('test', file_get_contents($path));
        } finally {
            @unlink($path);
        }
    }

    public function testSupportsStreamWrappers(): void
    {
        $writer = new FileWriter('php://temp');
        $writer->write('XY');
        $writer->flush();

        $fd = $writer->getFileDescriptor();
        rewind($fd);
        $content = stream_get_contents($fd);
        $writer->close();

        self::assertSame('XY', $content);
    }
}
