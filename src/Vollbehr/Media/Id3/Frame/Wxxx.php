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
 * This frame is intended for URL links concerning the audio file in a similar
 * way to the other 'W'-frames. The frame body consists of a description of the
 * string followed by the actual URL. The URL is always encoded with ISO-8859-1.
 * There may be more than one 'WXXX' frame in each tag, but only one with the
 * same description.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Wxxx extends \Vollbehr\Media\Id3\LinkFrame implements \Vollbehr\Media\Id3\Encoding
{
    /** @var integer */
    private $_encoding;

    /** @var string */
    private $_description;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        \Vollbehr\Media\Id3\Frame::__construct($reader, $options);
        $this->setEncoding($this->getOption('encoding', \Vollbehr\Media\Id3\Encoding::UTF8));

        if ($this->_reader === null) {
            return;
        }

        $encoding                           = $this->_reader->readUInt8();
        [$this->_description, $this->_link] = match ($encoding) {
            self::UTF16, self::UTF16BE => $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 2),
            default => $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2),
        };
        $this->_description = $this->_convertString($this->_description, $encoding);
        $this->_link        = implode('', $this->_explodeString8($this->_link, 1));
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
     * Returns the link description.
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
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16(
                $this->_description,
                \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                1
            ),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_description, null, 1),
            default => $writer->writeString8($this->_description, 1),
        };
        $writer->write($this->_link);
    }
}
