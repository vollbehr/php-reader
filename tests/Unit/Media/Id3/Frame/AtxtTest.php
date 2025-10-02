<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Media\Id3\Frame;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\StringReader;
use Vollbehr\Io\StringWriter;
use Vollbehr\Media\Id3\Encoding;
use Vollbehr\Media\Id3\Frame\Atxt;

final class AtxtTest extends TestCase
{
    public function testParsesScrambledAudioFrame(): void
    {
        $options          = ['version' => 4, 'encoding' => 'utf-8'];
        $unscrambledAudio = "\x12\x34\x56";

        $prototype = new Atxt(null, $options);
        $prototype->setEncoding(Encoding::UTF8);
        $prototype->setMimeType('audio/mpeg');
        $prototype->setEquivalentText('Title');
        $prototype->setAudioData($unscrambledAudio);
        $prototype->setScramblingApplied(true);

        $prototypeWriter = new StringWriter();
        $prototype->write($prototypeWriter);
        $frameBinary = (string) $prototypeWriter->toString();
        $payload     = substr($frameBinary, 10);

        $options     = ['version' => 4, 'encoding' => 'utf-8'];

        $frame = new Atxt(new StringReader($frameBinary), $options);

        self::assertSame('audio/mpeg', $frame->getMimeType());
        self::assertTrue($frame->isScramblingApplied());
        self::assertSame('Title', $frame->getEquivalentText());
        self::assertSame($unscrambledAudio, $frame->getAudioData());
        self::assertSame("\xec\x30\x4e", substr($payload, -strlen($unscrambledAudio)));
    }

    public function testWriteScramblesAudioPayload(): void
    {
        $options          = ['version' => 4, 'encoding' => 'utf-8'];
        $frame            = new Atxt(null, $options);
        $unscrambledAudio = "\x12\x34\x56";
        $frame->setEncoding(Encoding::UTF8);
        $frame->setMimeType('audio/mpeg');
        $frame->setEquivalentText('Title');
        $frame->setAudioData($unscrambledAudio);
        $frame->setScramblingApplied(true);

        $writer = new StringWriter();
        $frame->write($writer);
        $binary  = $writer->toString();
        $payload = substr((string) $binary, 10);

        $scrambledAudio = substr((string) $payload, -strlen($unscrambledAudio));
        self::assertGreaterThanOrEqual(3, strlen((string) $payload));
        self::assertSame("\xec\x30\x4e", $scrambledAudio);

        $parsed = new Atxt(new StringReader((string) $binary), $options);
        self::assertTrue($parsed->isScramblingApplied());
        self::assertSame($unscrambledAudio, $parsed->getAudioData());
    }

    public function testWriteWithoutScramblingKeepsAudioUntouched(): void
    {
        $options = ['version' => 4, 'encoding' => 'utf-8'];
        $frame   = new Atxt(null, $options);
        $frame->setEncoding(Encoding::UTF8);
        $frame->setMimeType('audio/wav');
        $frame->setEquivalentText('Narration');
        $frame->setAudioData("\xaa\xbb");
        $frame->setScramblingApplied(false);

        $writer = new StringWriter();
        $frame->write($writer);
        $binary  = $writer->toString();
        $payload = substr((string) $binary, 10);

        self::assertGreaterThanOrEqual(2, strlen((string) $payload));
        self::assertSame("\xaa\xbb", substr((string) $payload, -2));

        $parsed = new Atxt(new StringReader((string) $binary), $options);
        self::assertFalse($parsed->isScramblingApplied());
        self::assertSame("\xaa\xbb", $parsed->getAudioData());
    }

    public function testScramblingHandlesLargePayloadsQuickly(): void
    {
        $options = ['version' => 4, 'encoding' => 'utf-8'];
        $audio   = str_repeat("AB", 4096); // 8192 bytes

        $frame = new Atxt(null, $options);
        $frame->setEncoding(Encoding::UTF8);
        $frame->setMimeType('audio/aac');
        $frame->setEquivalentText('Long description');
        $frame->setAudioData($audio);
        $frame->setScramblingApplied(true);

        $writer = new StringWriter();
        $frame->write($writer);
        $binary = (string) $writer->toString();

        $parsed = new Atxt(new StringReader($binary), $options);

        self::assertTrue($parsed->isScramblingApplied());
        self::assertSame($audio, $parsed->getAudioData());
    }

}
