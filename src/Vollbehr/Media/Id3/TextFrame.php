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
 * Ensures that frame payloads that are written back to disk respect the
 * ID3v2.x character encoding limits while still exposing text data using the
 * tag-level encoding preference.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
abstract class TextFrame extends Frame implements Encoding
{
    /**
     * The text encoding.
     * @var int
     */
    protected int $_encoding;

    /**
     * The text array.
     * @var array<int, string>
     */
    protected array $_text = [];

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
        $this->_text     = match ($encoding) {
            self::UTF16, self::UTF16BE => $this->_convertString(
                $this->_explodeString16($this->_reader->readString16($this->_reader->getSize())),
                $encoding
            ),
            default => $this->_convertString(
                $this->_explodeString8($this->_reader->readString8($this->_reader->getSize())),
                $encoding
            ),
        };

        if (!is_array($this->_text)) {
            $this->_text = [(string) $this->_text];
        }
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
     * Returns the first text chunk the frame contains.
     * @return string
     */
    public function getText()
    {
        return $this->_text[0] ?? '';
    }

    /**
     * Returns an array of texts the frame contains.
     * @return array<int, string>
     */
    public function getTexts()
    {
        return $this->_text;
    }

    /**
     * Sets the text using given encoding.
     * @param array<int, string>|string $text The text string or an array of strings.
     * @param int|string|null $encoding The text encoding.
     */
    public function setText($text, $encoding = null): void
    {
        $this->_text = is_array($text) ? array_values($text) : [(string) $text];
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

        $writer->writeUInt8($this->_encoding);
        switch ($this->_encoding) {
            case self::UTF16LE:
                $count = count($this->_text);
                for ($i = 0; $i < $count; $i++) {
                    $writer->writeString16(
                        $this->_text[$i],
                        \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                        $i === $count - 1 ? null : 1
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
     * @param array<int, string> $chunks
     * @param int $source
     * @param int $target
     * @return array<int, string>|null
     */
    private function tryConvertText(array $chunks, int $source, int $target): ?array
    {
        if ($chunks === []) {
            return [];
        }

        $sourceCharset = $this->_translateIntToEncoding($source);
        $targetCharset = $this->_translateIntToEncoding($target);
        $converted     = [];

        foreach ($chunks as $key => $chunk) {
            $chunkString = (string) $chunk;
            // iconv emits a notice on failure; suppress and inspect the return value instead.
            $result      = @iconv($sourceCharset, $targetCharset, $chunkString);
            if ($result === false) {
                return null;
            }
            $converted[$key] = $result;
        }

        return array_values($converted);
    }
}
