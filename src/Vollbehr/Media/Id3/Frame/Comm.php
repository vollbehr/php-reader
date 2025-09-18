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
 * The _Comments_ frame is intended for any kind of full text information
 * that does not fit in any other frame. It consists of a frame header followed
 * by encoding, language and content descriptors and is ended with the actual
 * comment as a text string. Newline characters are allowed in the comment text
 * string. There may be more than one comment frame in each tag, but only one
 * with the same language and content descriptor.
 * @author Sven Vollbehr
 */
final class Comm extends \Vollbehr\Media\Id3\LanguageTextFrame
{
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

        $encoding        = $this->_reader->readUInt8();
        $this->_language = strtolower($this->_reader->read(3));
        if ($this->_language === 'xxx') {
            $this->_language = 'und';
        }

        switch ($encoding) {
            case self::UTF16:
                // break intentionally omitted
            case self::UTF16BE:
                [$this->_description, $this->_text] = $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 2);
                $this->_description                 = $this->_convertString($this->_description, $encoding);
                $this->_text                        = $this->_convertString($this->_text, $encoding);
                break;
            case self::UTF8:
                // break intentionally omitted
            default:
                [$this->_description, $this->_text] = $this->_convertString(
                    $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2),
                    $encoding
                );
                break;
        }
    }

    /**
     * Returns the short content description.
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Sets the content description text using given encoding. The description
     * language and encoding must be that of the actual text.
     * @param string $description The content description text.
     * @param string $language The language code.
     * @param integer $encoding The text encoding.
     */
    public function setDescription($description, $language = null, $encoding = null): void
    {
        $this->_description = $description;
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
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding)
               ->write($this->_language);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16(
                $this->_description,
                \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                1
            )
                   ->writeString16($this->_text, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_description, null, 1)
                   ->writeString16($this->_text),
            default => $writer->writeString8($this->_description, 1)
                   ->writeString8($this->_text),
        };
    }
}
