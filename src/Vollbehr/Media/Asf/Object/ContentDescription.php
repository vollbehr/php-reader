<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf\Object;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Content Description Object_ lets authors record well-known data
 * describing the file and its contents. This object is used to store standard
 * bibliographic information such as title, author, copyright, description, and
 * rating information. This information is pertinent to the entire file.
 * @author Sven Vollbehr
 */
final class ContentDescription extends \Vollbehr\Media\Asf\BaseObject
{
    private string | false $_title;

    private string | false $_author;

    private string | false $_copyright;

    private string | false $_description;

    private string | false $_rating;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $titleLen       = $this->_reader->readUInt16LE();
        $authorLen      = $this->_reader->readUInt16LE();
        $copyrightLen   = $this->_reader->readUInt16LE();
        $descriptionLen = $this->_reader->readUInt16LE();
        $ratingLen      = $this->_reader->readUInt16LE();

        $this->_title = iconv(
            'utf-16le',
            (string) $this->getOption('encoding'),
            $this->_reader->readString16($titleLen)
        );
        $this->_author = iconv(
            'utf-16le',
            (string) $this->getOption('encoding'),
            $this->_reader->readString16($authorLen)
        );
        $this->_copyright = iconv(
            'utf-16le',
            (string) $this->getOption('encoding'),
            $this->_reader->readString16($copyrightLen)
        );
        $this->_description = iconv(
            'utf-16le',
            (string) $this->getOption('encoding'),
            $this->_reader->readString16($descriptionLen)
        );
        $this->_rating = iconv(
            'utf-16le',
            (string) $this->getOption('encoding'),
            $this->_reader->readString16($ratingLen)
        );
    }

    /**
     * Returns the title information.
     * @return string
     */
    public function getTitle(): string | bool
    {
        return $this->_title;
    }

    /**
     * Sets the title information.
     * @param string $title The title information.
     */
    public function setTitle(string | bool $title): void
    {
        $this->_title = $title;
    }

    /**
     * Returns the author information.
     * @return string
     */
    public function getAuthor(): string | bool
    {
        return $this->_author;
    }

    /**
     * Sets the author information.
     * @param string $author The author information.
     */
    public function setAuthor(string | bool $author): void
    {
        $this->_author = $author;
    }

    /**
     * Returns the copyright information.
     * @return string
     */
    public function getCopyright(): string | bool
    {
        return $this->_copyright;
    }

    /**
     * Sets the copyright information.
     * @param string $copyright The copyright information.
     */
    public function setCopyright(string | bool $copyright): void
    {
        $this->_copyright = $copyright;
    }

    /**
     * Returns the description information.
     * @return string
     */
    public function getDescription(): string | bool
    {
        return $this->_description;
    }

    /**
     * Sets the description information.
     * @param string $description The description information.
     */
    public function setDescription(string | bool $description): void
    {
        $this->_description = $description;
    }

    /**
     * Returns the rating information.
     * @return string
     */
    public function getRating(): string | bool
    {
        return $this->_rating;
    }

    /**
     * Sets the rating information.
     * @param string $rating The rating information.
     */
    public function setRating(string | bool $rating): void
    {
        $this->_rating = $rating;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $title = iconv(
            (string) $this->getOption('encoding'),
            'utf-16le',
            $this->_title ? $this->_title . "\0" : ''
        );
        $author = iconv(
            (string) $this->getOption('encoding'),
            'utf-16le',
            $this->_author ? $this->_author . "\0" : ''
        );
        $copyright = iconv(
            (string) $this->getOption('encoding'),
            'utf-16le',
            $this->_copyright ? $this->_copyright . "\0" : ''
        );
        $description = iconv(
            (string) $this->getOption('encoding'),
            'utf-16le',
            $this->_description ? $this->_description . "\0" : ''
        );
        $rating = iconv(
            (string) $this->getOption('encoding'),
            'utf-16le',
            $this->_rating ? $this->_rating . "\0" : ''
        );

        $buffer = new \Vollbehr\Io\StringWriter();
        $buffer->writeUInt16LE(strlen($title))
               ->writeUInt16LE(strlen($author))
               ->writeUInt16LE(strlen($copyright))
               ->writeUInt16LE(strlen($description))
               ->writeUInt16LE(strlen($rating))
               ->writeString16($title)
               ->writeString16($author)
               ->writeString16($copyright)
               ->writeString16($description)
               ->writeString16($rating);

        $this->setSize(24 /* for header */ + $buffer->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->write($buffer->toString());
    }
}
