<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Io;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\StringReader;

/**
 * @coversDefaultClass \Vollbehr\Io\StringReader
 */
final class StringReaderTest extends TestCase
{
    /**
     * @covers ::read
     * @covers ::getOffset
     */
    public function testReadAdvancesOffset(): void
    {
        $reader = new StringReader('abcd');

        self::assertSame('ab', $reader->read(2));
        self::assertSame(2, $reader->getOffset());
    }

    /**
     * @covers ::read
     * @covers ::toString
     * @covers ::getOffset
     */
    public function testToStringRestoresOffset(): void
    {
        $reader = new StringReader('hello');
        $reader->read(2);

        self::assertSame('hello', $reader->toString());
        self::assertSame(2, $reader->getOffset());
    }
}
