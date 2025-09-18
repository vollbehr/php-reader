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
 * This frame is intended for one-string text information concerning the audio
 * file in a similar way to the other T-frames. The frame consists of a
 * description of the string followed by the actual string. There may be more
 * than one TXXX frame in each tag, but only one with the same description.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Txxx extends \Vollbehr\Media\Id3\TextFrame
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

        $encoding = $this->_reader->readUInt8();
        switch ($encoding) {
            case self::UTF16:
                // break intentionally omitted
            case self::UTF16BE:
                [$this->_description, $this->_text] = $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 2);
                $this->_description                 = $this->_convertString($this->_description, $encoding);
                $this->_text                        = $this->_convertString([$this->_text], $encoding);
                break;
            case self::UTF8:
                // break intentionally omitted
            default:
                [$this->_description, $this->_text] = $this->_convertString(
                    $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2),
                    $encoding
                );
                $this->_text = [$this->_text];
                break;
        }
    }

    /**
     * Returns the description text.
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Sets the description text using given encoding.
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
                null,
                1
            )
                   ->writeString16(
                       $this->_text[0],
                       \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER
                   ),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_description, null, 1)
                   ->writeString16($this->_text[0], null),
            default => $writer->writeString8($this->_description, 1)
                   ->writeString8($this->_text[0]),
        };
    }
}
