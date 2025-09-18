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
 * A base class for all the multilanguage text frames.
 * @author Sven Vollbehr
 */
abstract class LanguageTextFrame extends Frame implements Encoding, Language
{
    /**
     * The text encoding.
     * @var integer
     */
    protected $_encoding;
    /**
     * The ISO-639-2 language code.
     * @var string
     */
    protected $_language = 'und';
    /**
     * The text.
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

        $encoding        = $this->_reader->readUInt8();
        $this->_language = strtolower($this->_reader->read(3));
        if ($this->_language === 'xxx') {
            $this->_language = 'und';
        }

        $this->_text = match ($encoding) {
            self::UTF16, self::UTF16BE => $this->_convertString(
                $this->_reader->readString16($this->_reader->getSize()),
                $encoding
            ),
            default => $this->_convertString(
                $this->_reader->readString8($this->_reader->getSize()),
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
     * @see Encoding
     * @param integer $encoding The text encoding.
     */
    public function setEncoding($encoding): void
    {
        $this->_encoding = $this->_translateEncodingToInt($encoding);
    }

    /**
     * Returns the language code as specified in the
     * {@see http://www.loc.gov/standards/iso639-2/ ISO-639-2} standard.
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Sets the text language code as specified in the
     * {@see http://www.loc.gov/standards/iso639-2/ ISO-639-2} standard.
     * @see Language
     * @param string $language The language code.
     */
    public function setLanguage($language): void
    {
        $language = strtolower($language);
        if ($language === 'xxx') {
            $language = 'und';
        }
        $this->_language = substr($language, 0, 3);
    }

    /**
     * Returns the text.
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Sets the text using given language and encoding.
     * @param string $text The text.
     * @param string $language The language code.
     * @param integer $encoding The text encoding.
     */
    public function setText($text, $language = null, $encoding = null): void
    {
        $this->_text = $text;
        if ($language !== null) {
            $this->setLanguage($language);
        }
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
        $writer->writeUInt8($this->_encoding)
               ->write($this->_language);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16($this->_text, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER),
            default => $writer->write($this->_text),
        };
    }
}
