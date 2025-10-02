<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Unit\Media\Id3\Frame;

use PHPUnit\Framework\TestCase;
use Vollbehr\Media\Id3\Frame\Tcon;
use Vollbehr\Media\Id3v1;

/**
 * @coversDefaultClass \Vollbehr\Media\Id3\Frame\Tcon
 */
final class TconTest extends TestCase
{
    /**
     * @covers ::setText
     * @covers ::getGenres
     * @covers ::getGenreCodes
     */
    public function testParsesId3v23Notation(): void
    {
        $frame = new Tcon();
        $frame->setText('(4)Eurodisco');

        self::assertSame(['Disco', 'Eurodisco'], $frame->getGenres());
        self::assertSame([4], $frame->getGenreCodes());
    }

    /**
     * @covers ::setText
     * @covers ::getGenres
     * @covers ::getGenreCodes
     */
    public function testParsesMultipleReferences(): void
    {
        $frame = new Tcon();
        $frame->setText('(0)(1)');

        self::assertSame(
            [
                Id3v1::$genres[0],
                Id3v1::$genres[1],
            ],
            $frame->getGenres()
        );
        self::assertSame([0, 1], $frame->getGenreCodes());
    }

    /**
     * @covers ::setText
     * @covers ::getGenres
     */
    public function testParsesEscapedLeadingParenthesis(): void
    {
        $frame = new Tcon();
        $frame->setText('((I think...)');

        self::assertSame(['(I think...)'], $frame->getGenres());
        self::assertSame([], $frame->getGenreCodes());
    }

    /**
     * @covers ::setText
     * @covers ::getGenres
     * @covers ::getGenreCodes
     */
    public function testParsesVersionFourNumericStrings(): void
    {
        $frame = new Tcon();
        $frame->setText(['21', 'Eurodisco']);

        self::assertSame(
            [
                Id3v1::$genres[21],
                'Eurodisco',
            ],
            $frame->getGenres()
        );
        self::assertSame([21], $frame->getGenreCodes());
    }

    /**
     * @covers ::setGenres
     * @covers ::getGenres
     * @covers ::getText
     */
    public function testSetGenresEncodesVersionThreeNotation(): void
    {
        $frame = new Tcon();
        $frame->setOption('version', 3.0);
        $frame->setGenres(['Disco', 'Eurodisco']);

        self::assertSame(['Disco', 'Eurodisco'], $frame->getGenres());
        self::assertSame('(4)Eurodisco', $frame->getText());
    }

    /**
     * @covers ::setGenres
     * @covers ::getGenres
     * @covers ::getText
     */
    public function testSetGenresEscapesLeadingParenthesisForVersionThree(): void
    {
        $frame = new Tcon();
        $frame->setOption('version', 3.0);
        $frame->setGenres(['(Live)']);

        self::assertSame(['(Live)'], $frame->getGenres());
        self::assertSame('((Live)', $frame->getText());
    }

    /**
     * @covers ::setGenres
     * @covers ::getGenres
     * @covers ::getTexts
     */
    public function testSetGenresEncodesVersionFourNumericStrings(): void
    {
        $frame = new Tcon();
        $frame->setOption('version', 4.0);
        $frame->setGenres(['Disco', 'Eurodisco']);

        self::assertSame(['Disco', 'Eurodisco'], $frame->getGenres());
        self::assertSame(['4', 'Eurodisco'], $frame->getTexts());
    }

    /**
     * @covers ::setGenres
     * @covers ::getGenres
     * @covers ::getTexts
     */
    public function testSetGenresNormalisesSpecialNames(): void
    {
        $frame = new Tcon();
        $frame->setOption('version', 4.0);
        $frame->setGenres(['Remix']);

        self::assertSame(['Remix'], $frame->getGenres());
        self::assertSame(['RX'], $frame->getTexts());
    }

    /**
     * @covers ::setGenres
     * @covers ::addGenre
     * @covers ::getGenres
     * @covers ::getTexts
     */
    public function testAddGenreAppendsToExistingList(): void
    {
        $frame = new Tcon();
        $frame->setOption('version', 4.0);
        $frame->setGenres([4]);
        $frame->addGenre('Eurodisco');

        self::assertSame(['Disco', 'Eurodisco'], $frame->getGenres());
        self::assertSame(['4', 'Eurodisco'], $frame->getTexts());
    }

    /**
     * @covers ::setGenres
     * @covers ::getGenres
     * @covers ::getTexts
     */
    public function testSetGenresIsCaseInsensitiveForId3v1Names(): void
    {
        $frame = new Tcon();
        $frame->setOption('version', 4.0);
        $frame->setGenres(['disco']);

        self::assertSame(['Disco'], $frame->getGenres());
        self::assertSame(['4'], $frame->getTexts());
        self::assertSame([4], $frame->getGenreCodes());
    }
}
