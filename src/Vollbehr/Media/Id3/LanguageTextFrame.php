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
 * Ensures language aware frames emit data using an encoding that is valid for
 * the current tag version while still exposing text in the tag level encoding.
 * @author Sven Vollbehr
 */
abstract class LanguageTextFrame extends Frame implements Encoding, Language
{
    /**
     * The text encoding.
     * @var int
     */
    protected int $_encoding;

    /**
     * The ISO-639-2 language code.
     * @var string
     */
    protected string $_language = 'und';

    /**
     * The text.
     * @var string
     */
    protected string $_text = '';

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_encoding = $this->determinePreferredEncoding();

        if ($this->_reader === null) {
            return;
        }

        $encoding        = $this->_reader->readUInt8();
        $this->_encoding = $encoding;
        $this->_language = strtolower($this->_reader->read(3));
        if ($this->_language === 'xxx') {
            $this->_language = 'und';
        }

        $this->_text = match ($encoding) {
            self::UTF16, self::UTF16BE => (string) $this->_convertString(
                $this->_reader->readString16($this->_reader->getSize()),
                $encoding
            ),
            default => (string) $this->_convertString(
                $this->_reader->readString8($this->_reader->getSize()),
                $encoding
            ),
        };
    }

    /**
     * Returns the text encoding as a charset string.
     * @return string
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
     * in the form accepted by iconv.
     * @see Encoding
     * @param int|string $encoding The text encoding.
     */
    public function setEncoding($encoding): void
    {
        $this->_encoding = $this->_translateEncodingToInt($encoding);
    }

    /**
     * Returns the text language code as specified in the
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
     * @param string|null $language The language code.
     * @param int|string|null $encoding The text encoding.
     */
    public function setText($text, $language = null, $encoding = null): void
    {
        $this->_text = (string) $text;
        if ($language !== null) {
            $this->setLanguage($language);
        }
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Aligns the in-memory encoding with the tag-level configuration prior to writes.
     */
    public function synchronizeEncodingWithTag(): void
    {
        $targetEncoding = $this->determinePreferredEncoding();
        if ($this->_encoding === $targetEncoding) {
            return;
        }

        $converted = $this->tryConvertText($this->_text, $this->_encoding, $targetEncoding);
        if ($converted === null && $this->getOption('version', 4) < 4 && $targetEncoding !== self::UTF16) {
            $fallbackEncoding = self::UTF16;
            $converted        = $this->tryConvertText($this->_text, $this->_encoding, $fallbackEncoding);
            if ($converted !== null) {
                $targetEncoding = $fallbackEncoding;
            }
        }

        if ($converted !== null) {
            $this->_text = $converted;
        }

        $this->_encoding = $targetEncoding;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer)
    {
        $this->synchronizeEncodingWithTag();

        $writer->writeUInt8($this->_encoding)
               ->write($this->_language);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16($this->_text, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER),
            default => $writer->write($this->_text),
        };
    }

    /**
     * Resolves the preferred encoding based on tag-level options and version constraints.
     */
    private function determinePreferredEncoding(): int
    {
        $version        = (float) $this->getOption('version', 4.0);
        $encodingOption = $this->getOption('encoding');

        if ($encodingOption === null) {
            return $version < 4.0 ? self::ISO88591 : self::UTF8;
        }

        $encoding = $this->_translateEncodingToInt($encodingOption);
        if ($version < 4.0 && $encoding !== self::ISO88591 && $encoding !== self::UTF16) {
            return self::ISO88591;
        }

        return $encoding;
    }

    /**
     * Attempts to convert the frame text to the requested encoding.
     * @param string $text
     * @param int $source
     * @param int $target
     * @return string|null
     */
    private function tryConvertText(string $text, int $source, int $target): ?string
    {
        if ($text === '') {
            return '';
        }

        $sourceCharset = $this->_translateIntToEncoding($source);
        $targetCharset = $this->_translateIntToEncoding($target);
        // iconv emits a notice on failure; suppress and inspect the return value instead.
        $result        = @iconv($sourceCharset, $targetCharset, $text);

        return $result === false ? null : $result;
    }
}
