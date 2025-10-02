<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Media\Iso14496;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\StringReader;
use Vollbehr\Media\Iso14496;

if (!class_exists('\Vollbehr\Media\Iso14496\Box\Box')) {
    class_alias(\Vollbehr\Media\Iso14496\Box::class, '\Vollbehr\Media\Iso14496\Box\Box');
}

final class LargeFileStringReader extends StringReader
{
    public function __construct(string $data, int $simulatedSize)
    {
        parent::__construct($data);
        $this->_size = self::normaliseLength($simulatedSize);
    }
}

/**
 * @covers \Vollbehr\Media\Iso14496
 */
final class Iso14496LargeFileTest extends TestCase
{
    /**
     * @covers \Vollbehr\Media\Iso14496
     * @covers \Vollbehr\Media\Iso14496\Box
     */
    public function testLargeFileStillExposesMetadataBoxes(): void
    {
        $data = $this->buildMinimalIso();
        $reader = new LargeFileStringReader($data, -854518697);

        $iso = new Iso14496($reader);

        self::assertGreaterThan(2_000_000_000, $reader->getSize());
        self::assertTrue(isset($iso->moov));
        self::assertTrue(isset($iso->moov->udta));
        self::assertTrue(isset($iso->moov->udta->meta));
        self::assertTrue(isset($iso->moov->udta->meta->ilst));
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
