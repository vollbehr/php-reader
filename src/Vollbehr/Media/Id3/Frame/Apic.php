<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * The _Attached picture_ frame contains a picture directly related to the
 * audio file. Image format is the MIME type and subtype for the image.
 * There may be several pictures attached to one file, each in their individual
 * APIC frame, but only one with the same content descriptor. There may only
 * be one picture with the same picture type.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Apic extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Encoding
{
    /**
     * The list of image types.
     * @var Array
     */
    public static $types = ['Other', '32x32 pixels file icon (PNG only)', 'Other file icon',
         'Cover (front)', 'Cover (back)', 'Leaflet page',
         'Media (e.g. label side of CD)', 'Lead artist/lead performer/soloist',
         'Artist/performer', 'Conductor', 'Band/Orchestra', 'Composer',
         'Lyricist/text writer', 'Recording Location', 'During recording',
         'During performance', 'Movie/video screen capture',
         'A bright coloured fish', 'Illustration', 'Band/artist logotype',
         'Publisher/Studio logotype'];

    /** @var integer */
    private $_encoding;

    /** @var string */
    private $_mimeType = 'image/unknown';

    /** @var integer */
    private $_imageType = 0;

    /** @var string */
    private $_description;

    /** @var string */
    private $_imageData;

    private int $_imageSize;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @todo  There is the possibility to put only a link to the image file by
     *  using the MIME type '-->' and having a complete URL instead of picture
     *  data. Support for such needs design considerations.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setEncoding($this->getOption('encoding', \Vollbehr\Media\Id3\Encoding::UTF8));

        if ($this->_reader === null) {
            return;
        }

        $encoding          = $this->_reader->readUInt8();
        [$this->_mimeType] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset(1 + strlen((string) $this->_mimeType) + 1);
        $this->_imageType = $this->_reader->readUInt8();

        [$this->_description, $this->_imageData] = match ($encoding) {
            self::UTF16, self::UTF16BE => $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 2),
            default => $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2),
        };
        $this->_description = $this->_convertString($this->_description, $encoding);
        $this->_imageSize   = strlen((string) $this->_imageData);
    }

    /**
     * Returns the text encoding.
     * All the strings read from a file are automatically converted to the
     * character encoding specified with the <var>encoding</var> option. See
     * {@see \Vollbehr\Media\Id3v2} for details. This method returns that character
     * encoding, or any value set after read, translated into a string form
     * regarless if it was set using a {@see \Vollbehr\Media\Id3\Encoding} constant
     * or a string.
     * @return integer
     */
    public function getEncoding()
    {
        return $this->_translateIntToEncoding($this->_encoding);
    }

    /**
     * Sets the text encoding.
     * All the string written to the frame are done so using given character
     * encoding. No conversions of existing data take place upon the call to
     * this method thus all texts must be given in given character encoding.
     * The character encoding parameter takes either a
     * {@see \Vollbehr\Media\Id3\Encoding} constant or a character set name string
     * in the form accepted by iconv. The default character encoding used to
     * write the frame is 'utf-8'.
     * @see \Vollbehr\Media\Id3\Encoding
     * @param integer $encoding The text encoding.
     */
    public function setEncoding($encoding): void
    {
        $this->_encoding = $this->_translateEncodingToInt($encoding);
    }

    /**
     * Returns the MIME type. The MIME type is always ISO-8859-1 encoded.
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }

    /**
     * Sets the MIME type. The MIME type is always ISO-8859-1 encoded.
     * @param string $mimeType The MIME type.
     */
    public function setMimeType($mimeType): void
    {
        $this->_mimeType = $mimeType;
    }

    /**
     * Returns the image type.
     * @return integer
     */
    public function getImageType()
    {
        return $this->_imageType;
    }

    /**
     * Sets the image type code.
     * @param integer $imageType The image type code.
     */
    public function setImageType($imageType): void
    {
        $this->_imageType = $imageType;
    }

    /**
     * Returns the file description.
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Sets the content description text using given encoding.
     * @param string $description The content description text.
     * @param integer $encoding The text encoding.
     */
    public function setDescription($description, $encoding = null): void
    {
        $this->_description = $description;
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Returns the embedded image data.
     * @return string
     */
    public function getImageData()
    {
        return $this->_imageData;
    }

    /**
     * Sets the embedded image data. Also updates the image size field to
     * correspond the new data.
     * @param string $imageData The image data.
     */
    public function setImageData($imageData): void
    {
        $this->_imageData = $imageData;
        $this->_imageSize = strlen($imageData);
    }

    /**
     * Returns the size of the embedded image data.
     */
    public function getImageSize(): int
    {
        return $this->_imageSize;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding)
               ->writeString8($this->_mimeType, 1)
               ->writeUInt8($this->_imageType);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16(
                $this->_description,
                \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                1
            ),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_description, null, 1),
            default => $writer->writeString8($this->_description, 1),
        };
        $writer->write($this->_imageData);
    }
}
