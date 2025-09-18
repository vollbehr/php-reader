<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * A base class for all the text frames.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
abstract class TextFrame extends Frame implements Encoding
{
    /**
     * The text encoding.
     * @var integer
     */
    protected $_encoding;
    /**
     * The text array.
     * @var string
     */
    protected $_text;
    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setEncoding($this->getOption('encoding', Encoding::UTF8));

        if ($this->_reader === null) {
            return;
        }

        $encoding    = $this->_reader->readUInt8();
        $this->_text = match ($encoding) {
            self::UTF16, self::UTF16BE => $this->_convertString(
                $this->_explodeString16($this->_reader->readString16($this->_reader->getSize())),
                $encoding
            ),
            default => $this->_convertString(
                $this->_explodeString8($this->_reader->readString8($this->_reader->getSize())),
                $encoding
            ),
        };
    }

    /**
     * Returns the text encoding.
     * All the strings read from a file are automatically converted to the
     * character encoding specified with the <var>encoding</var> option. See
     * {@see \Vollbehr\Media\Id3v2} for details. This method returns that character
     * encoding, or any value set after read, translated into a string form
     * regardless if it was set using a {@see \Vollbehr\Media\Id3\Encoding} constant
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
     * @see Encoding
     * @param integer $encoding The text encoding.
     */
    public function setEncoding($encoding): void
    {
        $this->_encoding = $this->_translateEncodingToInt($encoding);
    }

    /**
     * Returns the first text chunk the frame contains.
     * @return string
     */
    public function getText()
    {
        return $this->_text[0];
    }

    /**
     * Returns an array of texts the frame contains.
     * @return Array
     */
    public function getTexts()
    {
        return $this->_text;
    }

    /**
     * Sets the text using given encoding.
     * @param mixed $text The text string or an array of strings.
     * @param integer $encoding The text encoding.
     */
    public function setText($text, $encoding = null): void
    {
        $this->_text = is_array($text) ? $text : [$text];
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    protected function _writeData($writer)
    {
        $writer->writeUInt8($this->_encoding);
        switch ($this->_encoding) {
            case self::UTF16LE:
                $count = count($this->_text);
                for ($i = 0; $i < $count; $i++) {
                    $writer->writeString16(
                        $text,
                        \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                        $i == $count ? null : 1
                    );
                }
                break;
            case self::UTF16:
                // break intentionally omitted
            case self::UTF16BE:
                $writer->write(implode("\0\0", $this->_text));
                break;
            default:
                $writer->write(implode("\0", $this->_text));
                break;
        }
    }
}
