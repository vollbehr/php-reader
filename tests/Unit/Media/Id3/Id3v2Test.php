<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Media\Id3;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\StringReader;
use Vollbehr\Media\Id3\Exception as Id3Exception;
use Vollbehr\Media\Id3v2;

/**
 * @coversDefaultClass \Vollbehr\Media\Id3v2
 */
final class Id3v2Test extends TestCase
{
    /**
     * @dataProvider provideValidTags
     * @covers ::__construct
     * @covers ::getFrames
     * @covers ::hasFrame
     * @covers ::getFramesByIdentifier
     * @covers ::hasExtendedHeader
     * @covers ::getExtendedHeader
     */
    public function testParsesTagVariants(
        string $binaryTag,
        array $expectedFrameTexts,
        bool $expectsExtendedHeader
    ): void {
        $reader = new StringReader($binaryTag);

        $id3 = new Id3v2($reader);

        self::assertSame($expectsExtendedHeader, $id3->hasExtendedHeader());
        self::assertSame(4, $id3->getHeader()->getVersion());

        foreach ($expectedFrameTexts as $identifier => $text) {
            self::assertTrue($id3->hasFrame($identifier));
            $frames = $id3->getFramesByIdentifier($identifier);
            self::assertNotEmpty($frames);
            $firstFrame = $frames[0];
            if (method_exists($firstFrame, 'getText')) {
                self::assertSame($text, $firstFrame->getText());
            }
        }

        self::assertCount(count($expectedFrameTexts), $id3->getFrames());
    }

    /**
     * @covers ::__construct
     */
    public function testThrowsWhenHeaderMissing(): void
    {
        $reader = new StringReader('NOT');

        $this->expectException(Id3Exception::class);
        $this->expectExceptionMessage('File does not contain ID3v2 tag');
        new Id3v2($reader);
    }

    /**
     * @dataProvider provideUnsupportedVersions
     * @covers ::__construct
     */
    public function testRejectsUnsupportedVersions(string $binaryTag): void
    {
        $reader = new StringReader($binaryTag);

        $this->expectException(Id3Exception::class);
        $this->expectExceptionMessage('supported version');
        new Id3v2($reader);
    }

    public static function provideValidTags(): array
    {
        return [
            'empty_tag' => [
                self::buildTag(body: ''),
                [],
                false,
            ],
            'single_text_frame' => [
                self::buildTag(body: self::buildTextFrame('TIT2', 'Test Title')),
                ['TIT2' => 'Test Title'],
                false,
            ],
            'with_extended_header' => [
                self::buildTag(
                    flags: 0x40,
                    body: self::buildExtendedHeader() .
                        self::buildTextFrame('TALB', 'Album Name')
                ),
                ['TALB' => 'Album Name'],
                true,
            ],
        ];
    }

    public static function provideUnsupportedVersions(): array
    {
        $emptyBody = '';

        return [
            'too_old' => [self::buildTag(version: 2, body: $emptyBody)],
            'too_new' => [self::buildTag(version: 5, body: $emptyBody)],
        ];
    }

    private static function buildTag(int $version = 4, int $flags = 0, string $body = ''): string
    {
        $size = self::encodeSynchsafe(strlen($body));

        return 'ID3' . chr($version) . "\x00" . chr($flags) . $size . $body;
    }

    private static function buildTextFrame(string $identifier, string $text): string
    {
        $payload = "\x00" . $text . "\x00";
        $size    = self::encodeSynchsafe(strlen($payload));

        return $identifier . $size . "\x00\x00" . $payload;
    }

    private static function buildExtendedHeader(): string
    {
        return self::encodeSynchsafe(6) . "\x01" . "\x00";
    }

    private static function encodeSynchsafe(int $value): string
    {
        return chr(($value >> 21) & 0x7f)
            . chr(($value >> 14) & 0x7f)
            . chr(($value >> 7) & 0x7f)
            . chr($value & 0x7f);
    }
}
