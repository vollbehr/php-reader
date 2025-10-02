<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Media\Iso14496;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\StringReader;
use Vollbehr\Media\Iso14496;

if (!class_exists('\Vollbehr\Media\Iso14496\Box\Box')) {
    class_alias(\Vollbehr\Media\Iso14496\Box::class, '\Vollbehr\Media\Iso14496\Box\Box');
}

/**
 * @covers \Vollbehr\Media\Iso14496
 * @covers \Vollbehr\Media\Iso14496\Box
 */
final class Iso14496MemoryTest extends TestCase
{
    public function testRepeatedParsingDoesNotGrowMemory(): void
    {
        if (!function_exists('gc_collect_cycles')) {
            $this->markTestSkipped('gc_collect_cycles is required for this test.');
        }

        $data = $this->buildMinimalIso();
        $baseline = memory_get_usage();
        $peak = $baseline;
        $firstRunUsage = null;

        for ($i = 0; $i < 20; $i++) {
            $reader = new StringReader($data);
            $iso = new Iso14496($reader);

            unset($iso, $reader);
            gc_collect_cycles();

            $current = memory_get_usage();
            $peak = max($peak, $current);
            if ($firstRunUsage === null) {
                $firstRunUsage = $current;
            }
        }

        $reference = $firstRunUsage ?? $baseline;
        self::assertLessThan($reference + 256 * 1024, $peak);
    }

    private function buildMinimalIso(): string
    {
        $ilst = $this->box('ilst', '');
        $metaPayload = pack('N', 0) . $ilst;
        $meta = $this->box('meta', $metaPayload);
        $udta = $this->box('udta', $meta);
        $moov = $this->box('moov', $udta);

        $ftypPayload = 'isom' . "\x00\x00\x02\x00" . 'mp41' . 'isom';
        $ftyp = $this->box('ftyp', $ftypPayload);
        $mdat = $this->box('mdat', '', 0);

        return $ftyp . $moov . $mdat;
    }

    private function box(string $type, string $payload, ?int $sizeOverride = null): string
    {
        $size = $sizeOverride ?? 8 + strlen($payload);

        return pack('N', $size) . $type . $payload;
    }
}
