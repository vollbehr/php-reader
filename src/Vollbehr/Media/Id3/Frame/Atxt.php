<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * The _Audio-text_ frame links a short narration clip to the text contained in
 * another frame, enhancing accessibility for visually impaired listeners.
 * It is defined by the ID3v2 Accessibility Addendum and uses the frame
 * identifier "ATXT".
 *
 * @see https://id3.org/id3v2-accessibility-1.0
 * @author Sven Vollbehr
 */
final class Atxt extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Encoding
{
    private const FLAG_SCRAMBLING = 0x01;
    private const SCRAMBLE_PERIOD = 127;

    /**
     * Cached pseudo-random scramble sequence covering one full 127-byte cycle.
     */
    private static ?string $scramblePattern = null;

    private int $_encoding;

    private string $_mimeType = 'audio/unknown';

    private string $_equivalentText = '';

    private string $_audioData = '';

    private bool $_scramblingApplied = false;

    /**
     * Parses the frame payload or prepares an empty frame instance.
     *
     * @param \Vollbehr\Io\Reader|null $reader
     * @param array<string, mixed>      $options
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setEncoding($this->getOption('encoding', \Vollbehr\Media\Id3\Encoding::UTF8));

        if ($this->_reader === null) {
            return;
        }

        $this->_encoding = $this->_reader->readUInt8();

        $payload   = $this->_reader->read($this->_reader->getSize()) ?? '';
        $mimeParts = $this->_explodeString8($payload, 2);
        $this->_mimeType = (string) ($mimeParts[0] ?? $this->_mimeType);
        if ($this->_mimeType === '') {
            $this->_mimeType = 'audio/unknown';
        }

        $rest = $mimeParts[1] ?? '';
        if ($rest === '') {
            $flags       = 0;
            $textPayload = '';
        } else {
            $flags       = ord($rest[0]);
            $textPayload = substr($rest, 1);
        }
        $this->_scramblingApplied = ($flags & self::FLAG_SCRAMBLING) === self::FLAG_SCRAMBLING;

        $segments = match ($this->_encoding) {
            self::UTF16LE, self::UTF16, self::UTF16BE => $this->_explodeString16($textPayload, 2),
            default => $this->_explodeString8($textPayload, 2),
        };
        $rawText   = $segments[0] ?? '';
        $audioData = $segments[1] ?? '';

        $this->_equivalentText = (string) $this->_convertString($rawText, $this->_encoding);
        $this->_audioData      = (string) $audioData;
        if ($this->_scramblingApplied && $this->_audioData !== '') {
            $this->_audioData = $this->applyScrambling($this->_audioData);
        }
    }

    /**
     * Returns the text encoding identifier as a charset string.
     */
    public function getEncoding()
    {
        return $this->_translateIntToEncoding($this->_encoding);
    }

    /**
     * Sets the encoding to use for the equivalent text field.
     *
     * @param int|string $encoding
     */
    public function setEncoding($encoding): void
    {
        $this->_encoding = $this->_translateEncodingToInt($encoding);
    }

    /**
     * Returns the MIME type describing the audio clip payload.
     */
    public function getMimeType(): string
    {
        return $this->_mimeType;
    }

    /**
     * Sets the MIME type representing the audio clip format.
     */
    public function setMimeType(string $mimeType): void
    {
        $this->_mimeType = $mimeType;
    }

    /**
     * Checks whether the accessibility scrambling scheme is enabled.
     */
    public function isScramblingApplied(): bool
    {
        return $this->_scramblingApplied;
    }

    /**
     * Enables or disables the accessibility scrambling scheme.
     */
    public function setScramblingApplied(bool $scramblingApplied): void
    {
        $this->_scramblingApplied = $scramblingApplied;
    }

    /**
     * Returns the equivalent text string used to match other frames.
     */
    public function getEquivalentText(): string
    {
        return $this->_equivalentText;
    }

    /**
     * Sets the equivalent text string.
     *
     * @param string        $equivalentText
     * @param int|string|null $encoding Optional override for the stored encoding.
     */
    public function setEquivalentText(string $equivalentText, $encoding = null): void
    {
        $this->_equivalentText = $equivalentText;
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Returns the decoded (unscrambled) audio clip payload.
     */
    public function getAudioData(): string
    {
        return $this->_audioData;
    }

    /**
     * Sets the audio clip payload. Provide the data in unscrambled form; the
     * frame applies scrambling automatically if required during writes.
     */
    public function setAudioData(string $audioData): void
    {
        $this->_audioData = $audioData;
    }

    /**
     * Writes the frame body without the header.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding)
               ->writeString8($this->_mimeType, 1)
               ->writeUInt8($this->_scramblingApplied ? self::FLAG_SCRAMBLING : 0);

        $encodedText = $this->encodeEquivalentTextForWriting();
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16(
                $encodedText,
                \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                1
            ),
            self::UTF16, self::UTF16BE => $writer->writeString16($encodedText, null, 1),
            default => $writer->writeString8($encodedText, 1),
        };

        $audioPayload = $this->_scramblingApplied ? $this->applyScrambling($this->_audioData) : $this->_audioData;
        $writer->write($audioPayload);
    }

    /**
     * Converts the equivalent text into the frame's storage encoding.
     */
    private function encodeEquivalentTextForWriting(): string
    {
        $sourceOption = $this->getOption('encoding', \Vollbehr\Media\Id3\Encoding::UTF8);
        $sourceCharset = $this->_translateIntToEncoding($sourceOption);
        $targetCharset = $this->_translateIntToEncoding($this->_encoding);

        if ($sourceCharset === $targetCharset) {
            return $this->_equivalentText;
        }

        $converted = @iconv($sourceCharset, $targetCharset, $this->_equivalentText);

        return $converted === false ? $this->_equivalentText : $converted;
    }

    /**
     * Builds the pseudo-random byte stream used for scrambling/descrambling.
     */
    private function generateScramblingSequence(int $length): string
    {
        if ($length <= 0) {
            return '';
        }

        $pattern       = $this->getScramblePattern();
        $patternLength = strlen($pattern);

        $repetitions = intdiv($length, $patternLength);
        $remainder   = $length % $patternLength;

        $sequence = $repetitions > 0 ? str_repeat($pattern, $repetitions) : '';
        if ($remainder > 0) {
            $sequence .= substr($pattern, 0, $remainder);
        }

        return $sequence;
    }

    /**
     * Derives the next byte in the scrambling sequence as per the addendum.
     */
    private function nextScramblingByte(int $current): int
    {
        $bit7 = ((($current >> 6) & 1) ^ (($current >> 5) & 1)) << 7;
        $bit6 = ((($current >> 5) & 1) ^ (($current >> 4) & 1)) << 6;
        $bit5 = ((($current >> 4) & 1) ^ (($current >> 3) & 1)) << 5;
        $bit4 = ((($current >> 3) & 1) ^ (($current >> 2) & 1)) << 4;
        $bit3 = ((($current >> 2) & 1) ^ (($current >> 1) & 1)) << 3;
        $bit2 = ((($current >> 1) & 1) ^ ($current & 1)) << 2;
        $bit1 = ((($current >> 7) & 1) ^ (($current >> 5) & 1)) << 1;
        $bit0 = ((($current >> 6) & 1) ^ (($current >> 4) & 1));

        return $bit7 | $bit6 | $bit5 | $bit4 | $bit3 | $bit2 | $bit1 | $bit0;
    }

    /**
     * Applies the scrambling XOR stream to the given audio payload.
     */
    private function applyScrambling(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $sequence = $this->generateScramblingSequence(strlen($data));

        return $data ^ $sequence;
    }

    /**
     * Returns the cached scramble pattern (127 bytes) generating new on demand.
     */
    private function getScramblePattern(): string
    {
        if (self::$scramblePattern !== null) {
            return self::$scramblePattern;
        }

        $bytes   = [0xfe];
        $current = 0xfe;
        for ($i = 1; $i < self::SCRAMBLE_PERIOD; $i++) {
            $current = $this->nextScramblingByte($current);
            $bytes[] = $current;
        }

        self::$scramblePattern = pack('C*', ...$bytes);

        return self::$scramblePattern;
    }
}
