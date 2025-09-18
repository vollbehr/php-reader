<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Integration\Media\Mpeg;

use PHPUnit\Framework\TestCase;
use Vollbehr\Media\Mpeg\Abs;

/**
 * @coversNothing
 */
final class AbsIntegrationTest extends TestCase
{
    public function testParsesMinimalMpegStream(): void
    {
        $frameCount = 2;
        $binary     = $this->buildMpegStream($frameCount);
        $path       = tempnam(sys_get_temp_dir(), 'mpeg');
        self::assertIsString($path);

        try {
            file_put_contents($path, $binary);

            $mpeg   = new Abs($path, ['readmode' => 'full']);
            $frames = $mpeg->getFrames();

            self::assertCount($frameCount, $frames);
            self::assertSame(417, $frames[0]->getLength());
            self::assertSame(128, $frames[0]->getBitrate());
            self::assertSame(44100, $frames[0]->getSamplingFrequency());
        } finally {
            @unlink($path);
        }
    }

    private function buildMpegStream(int $frameCount): string
    {
        $frame = $this->buildMpegFrame();

        return str_repeat($frame, $frameCount);
    }

    private function buildMpegFrame(): string
    {
        $bitrate    = 128000; // bits per second
        $sampleRate = 44100;
        $padding    = 0;

        $header = $this->buildMpegHeader($bitrateIndex = 9, $sampleRateIndex = 0, $padding);

        $frameLength   = (int) floor((144 * $bitrate) / $sampleRate);
        $payloadLength = $frameLength - 4;

        return $header . str_repeat("\x00", $payloadLength);
    }

    private function buildMpegHeader(int $bitrateIndex, int $sampleRateIndex, int $paddingBit): string
    {
        $header = 0xFFE00000;             // frame sync
        $header |= 0x3 << 19;             // MPEG Version 1
        $header |= 0x1 << 17;             // Layer III
        $header |= 0x1 << 16;             // No CRC protection
        $header |= $bitrateIndex << 12;   // Bitrate index (128 kbps)
        $header |= $sampleRateIndex << 10; // Sampling rate index (44.1 kHz)
        $header |= $paddingBit << 9;      // Padding
        $header |= 0 << 8;                // Private bit
        $header |= 0 << 6;                // Stereo
        $header |= 0 << 4;                // Mode extension
        $header |= 0 << 3;                // Copyright
        $header |= 0 << 2;                // Original
        $header |= 0;                     // Emphasis

        return pack('N', $header);
    }
}
