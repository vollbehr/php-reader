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
 * In the _General encapsulated object_ frame any type of file can be
 * encapsulated.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Geob extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Encoding
{
    /** @var integer */
    private $_encoding;

    /** @var string */
    private $_mimeType;

    /** @var string */
    private $_filename;

    /** @var string */
    private $_description;

    /** @var string */
    private $_data;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
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

        [$this->_filename, $this->_description, $this->_data] = match ($encoding) {
            self::UTF16, self::UTF16BE => $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 3),
            default => $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 3),
        };
        $this->_filename    = $this->_convertString($this->_filename, $encoding);
        $this->_description = $this->_convertString($this->_description, $encoding);
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
     * Returns the MIME type. The MIME type is always encoded with ISO-8859-1.
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
     * Returns the file name.
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Sets the file name using given encoding. The file name encoding must be
     * that of the description text.
     * @param integer $encoding The text encoding.
     */
    public function setFilename($filename, $encoding = null): void
    {
        $this->_filename = $filename;
        if ($encoding !== null) {
            $this->_encoding = $encoding;
        }
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
     * Sets the file description text using given encoding. The description
     * encoding must be that of the file name.
     * @param string $description The file description text.
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
     * Returns the embedded object binary data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the embedded object binary data.
     * @param string $data The object data.
     */
    public function setData($data): void
    {
        $this->_data = $data;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding)
               ->writeString8($this->_mimeType, 1);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16($this->_filename, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER, 1)
                   ->writeString16(
                       $this->_description,
                       \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                       1
                   ),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_filename, null, 1)
                   ->writeString16($this->_description, null, 1),
            default => $writer->writeString8($this->_filename, 1)
                   ->writeString8($this->_description, 1),
        };
        $writer->write($this->_data);
    }
}
