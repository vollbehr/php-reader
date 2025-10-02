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
 * The _Synchronised lyrics/text_ frame is another way of incorporating the
 * words, said or sung lyrics, in the audio file as text, this time, however,
 * in sync with the audio. It might also be used to describing events e.g.
 * occurring on a stage or on the screen in sync with the audio.
 * There may be more than one SYLT frame in each tag, but only one with the
 * same language and content descriptor.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Sylt extends \Vollbehr\Media\Id3\Frame implements
    \Vollbehr\Media\Id3\Encoding,
    \Vollbehr\Media\Id3\Language,
    \Vollbehr\Media\Id3\Timing
{
    /**
     * The list of content types.
     * @var Array
     */
    public static $types = ['Other', 'Lyrics', 'Text transcription', 'Movement/Part name',
         'Events', 'Chord', 'Trivia', 'URLs to webpages', 'URLs to images'];
    /** @var integer */
    private $_encoding;

    /** @var string */
    private $_language = 'und';

    /** @var integer */
    private $_format = \Vollbehr\Media\Id3\Timing::MPEG_FRAMES;

    /** @var integer */
    private $_type = 0;

    /** @var string */
    private $_description;

    /** @var Array */
    private $_events = [];

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

        $encoding        = $this->_reader->readUInt8();
        $this->_language = strtolower($this->_reader->read(3));
        if ($this->_language === 'xxx') {
            $this->_language = 'und';
        }
        $this->_format = $this->_reader->readUInt8();
        $this->_type   = $this->_reader->readUInt8();

        $offset = $this->_reader->getOffset();
        switch ($encoding) {
            case self::UTF16:
                // break intentionally omitted
            case self::UTF16BE:
                [$this->_description] = $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 2);
                $this->_reader->setOffset($offset + strlen((string) $this->_description) + 2);
                break;
            case self::UTF8:
                // break intentionally omitted
            default:
                [$this->_description] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
                $this->_reader->setOffset($offset + strlen((string) $this->_description) + 1);
                break;
        }
        $this->_description = $this->_convertString($this->_description, $encoding);

        while ($this->_reader->available()) {
            $offset = $this->_reader->getOffset();
            switch ($encoding) {
                case self::UTF16:
                    // break intentionally omitted
                case self::UTF16BE:
                    [$syllable] = $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 2);
                    $this->_reader->setOffset($offset + strlen((string) $syllable) + 2);
                    break;
                case self::UTF8:
                    // break intentionally omitted
                default:
                    [$syllable] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
                    $this->_reader->setOffset($offset + strlen((string) $syllable) + 1);
                    break;
            }
            $this->_events
                [$this->_reader->readUInt32BE()] = $this->_convertString($syllable, $encoding);
        }
        ksort($this->_events);
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
     * @see \Vollbehr\Media\Id3\Language
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
     * Returns the timing format.
     * @return integer
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the timing format.
     * @see \Vollbehr\Media\Id3\Timing
     * @param integer $format The timing format.
     */
    public function setFormat($format): void
    {
        $this->_format = $format;
    }

    /**
     * Returns the content type code.
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the content type code.
     * @param integer $type The content type code.
     */
    public function setType($type): void
    {
        $this->_type = $type;
    }

    /**
     * Returns the content description.
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
     * Returns the syllable events with their timestamps.
     * @return Array
     */
    public function getEvents()
    {
        return $this->_events;
    }

    /**
     * Sets the syllable events with their timestamps using given encoding.
     * The text language and encoding must be that of the description text.
     * @param string $language The language code.
     * @param integer $encoding The text encoding.
     */
    public function setEvents($events, $language = null, $encoding = null): void
    {
        $this->_events = $events;
        if ($language !== null) {
            $this->setLanguage($language);
        }
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
        ksort($this->_events);
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding)
               ->write($this->_language)
               ->writeUInt8($this->_format)
               ->writeUInt8($this->_type);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16(
                $this->_description,
                \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                1
            ),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_description, null, 1),
            default => $writer->writeString8($this->_description, 1),
        };
        foreach ($this->_events as $timestamp => $syllable) {
            match ($this->_encoding) {
                self::UTF16LE => $writer->writeString16($syllable, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER, 1),
                self::UTF16, self::UTF16BE => $writer->writeString16($syllable, null, 1),
                default => $writer->writeString8($syllable, 1),
            };
            $writer->writeUInt32BE($timestamp);
        }
    }
}
