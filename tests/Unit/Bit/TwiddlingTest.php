<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Bit;

use PHPUnit\Framework\TestCase;
use Vollbehr\Bit\Twiddling;

/**
 * @coversDefaultClass \Vollbehr\Bit\Twiddling
 */
final class TwiddlingTest extends TestCase
{
    /**
     * @covers ::getValue
     */
    public function testGetValueExtractsUnsignedFrameSync(): void
    {
        $header = 0xfffb5440;

        self::assertSame(0x7ff, Twiddling::getValue($header, 21, 31));
    }

    /**
     * @covers ::getValue
     */
    public function testGetValueExtractsFrameSyncFromSignedHeader(): void
    {
        $header = 0xfffb5440 - 0x100000000;

        self::assertSame(0x7ff, Twiddling::getValue($header, 21, 31));
    }

    /**
     * @covers ::setValue
     * @covers ::getValue
     * @covers ::getMask
     */
    public function testSetValueAndGetValueRoundTrip(): void
    {
        $value = Twiddling::setValue(0, 21, 31, 0x7ff);

        self::assertSame(0xffe00000, $value);
        self::assertSame(0x7ff, Twiddling::getValue($value, 21, 31));
    }
}
