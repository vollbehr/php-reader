<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3;

use Vollbehr\Io\Reader;
use Vollbehr\Io\Writer;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * The base class for all ID3v2 objects.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
abstract class BaseObject
{
    /**
     * Shared options available to the object graph.
     * @var array<string, mixed>
     */
    private array $_options = [];
    protected ?Reader $_reader = null;
    /**
     * Constructs the class with given parameters.
     */
    public function __construct(?Reader $reader = null, array &$options = [])
    {
        $this->_reader  = &$reader;
        $this->_options = &$options;
    }

    /**
     * Returns the options array.
     */
    final public function &getOptions(): array
    {
        return $this->_options;
    }

    /**
     * Returns the given option value, or the default value if the option is not
     * defined.
     * @param string $option The name of the option.
     * @param mixed $defaultValue The default value to be returned.
     */
    final public function getOption(string $option, mixed $defaultValue = null): mixed
    {
        return $this->_options[$option] ?? $defaultValue;
    }

    /**
     * Sets the options array. See {@see \Vollbehr\Media\Id3v2} class for available
     * options.
     */
    final public function setOptions(array &$options): void
    {
        $this->_options = &$options;
    }

    /**
     * Sets the given option the given value.
     * @param string $option The name of the option.
     * @param mixed $value The value to set for the option.
     */
    final public function setOption(string $option, mixed $value): void
    {
        $this->_options[$option] = $value;
    }

    /**
     * Clears the given option value.
     * @param string $option The name of the option.
     */
    final public function clearOption(string $option): void
    {
        unset($this->_options[$option]);
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The field name.
     */
    public function __get(string $name): mixed
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }

    /**
     * Magic function so that assignments with $obj->value will work.
     * @param string $name  The field name.
     * @param string $value The field value.
     */
    public function __set(string $name, mixed $value): void
    {
        if (method_exists($this, 'set' . ucfirst($name))) {
            call_user_func([$this, 'set' . ucfirst($name)], $value);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }

    /**
     * Encodes the given 32-bit integer to 28-bit synchsafe integer, where the
     * most significant bit of each byte is zero, making seven bits out of eight
     * available.
     */
    final protected function encodeSynchsafe32(int $val): int
    {
        return ($val & 0x7f) | ($val & 0x3f80) << 1 |
            ($val & 0x1fc000) << 2 | ($val & 0xfe00000) << 3;
    }

    /**
     * Decodes the given 28-bit synchsafe integer to regular 32-bit integer.
     */
    final protected function decodeSynchsafe32(int $val): int
    {
        return ($val & 0x7f) | ($val & 0x7f00) >> 1 |
            ($val & 0x7f0000) >> 2 | ($val & 0x7f000000) >> 3;
    }

    /**
     * Applies the unsynchronisation scheme to the given data string.
     * Whenever a false synchronisation is found within the data, one zeroed
     * byte is inserted after the first false synchronisation byte. This has the
     * side effect that all 0xff00 combinations have to be altered, so they will
     * not be affected by the decoding process.
     * Therefore all the 0xff00 combinations are replaced with the 0xff0000 combination and all the 0xff[0xe0-0xff]
     * combinations are replaced with 0xff00[0xe0-0xff] during the unsynchronisation.
     */
    final protected function encodeUnsynchronisation(string &$data): string
    {
        return preg_replace('/\xff(?=[\xe0-\xff])/', "\xff\x00", (string) preg_replace('/\xff\x00/', "\xff\x00\x00", $data));
    }

    /**
     * Reverses the unsynchronisation scheme from the given data string.
     */
    final protected function decodeUnsynchronisation(string &$data): string
    {
        return preg_replace('/\xff\x00\x00/', "\xff\x00", (string) preg_replace('/\xff\x00(?=[\xe0-\xff])/', "\xff", $data));
    }

    /**
     * Splits UTF-16 formatted binary data up according to null terminators
     * residing in the string, up to a given limit.
     */
    final protected function explodeString16(string $value, ?int $limit = null): array
    {
        $i     = 0;
        $array = [];
        while (count($array) < $limit - 1 || $limit === null) {
            $start = $i;
            do {
                $i = strpos($value, "\x00\x00", $i);
                if ($i === false) {
                    $array[] = substr($value, $start);

                    return $array;
                }
            } while ($i & 0x1 != 0 && $i++); // make sure its aligned
            $array[] = substr($value, $start, $i - $start);
            $i += 2;
        }
        $array[] = substr($value, $i);

        return $array;
    }

    /**
     * Splits UTF-8 or ISO-8859-1 formatted binary data according to null
     * terminators residing in the string, up to a given limit.
     */
    final protected function explodeString8(string $value, ?int $limit = null): array
    {
        return preg_split('/\x00/', $value, $limit ?? -1);
    }

    /**
     * Backwards compatible alias for legacy underscore-prefixed helper.
     */
    final protected function _explodeString16(string $value, ?int $limit = null): array
    {
        return $this->explodeString16($value, $limit);
    }

    /**
     * Backwards compatible alias for legacy underscore-prefixed helper.
     */
    final protected function _explodeString8(string $value, ?int $limit = null): array
    {
        return $this->explodeString8($value, $limit);
    }

    /**
     * Converts string from the given character encoding to the target encoding
     * specified by the options as the encoding to display all the texts with,
     * and returns the converted string.
     * Character encoding sets can be {@see \Vollbehr\Media\Id3\Encoding}
     * constants or already in the string form accepted by iconv.
     */
    final protected function convertString(string|array $string, string|int $source, string|int|null $target = null): array|string
    {
        if ($target === null) {
            $target = $this->getOption('encoding', 'utf-8');
        }

        $source = $this->translateIntToEncoding($source);
        $target = $this->translateIntToEncoding($target);

        if ($source == $target) {
            return $string;
        }

        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = iconv($source, $target, (string) $value);
            }
        } else {
            $string = iconv($source, $target, $string);
        }

        return $string;
    }

    /**
     * Backwards compatible alias for legacy underscore-prefixed helper.
     */
    final protected function _convertString(string|array $string, string|int $source, string|int|null $target = null): array|string
    {
        return $this->convertString($string, $source, $target);
    }

    /**
     * Returns given encoding in the form accepted by iconv.
     * Character encoding set can be a {@see \Vollbehr\Media\Id3\Encoding}
     * constant or already in the string form accepted by iconv.
     */
    final protected function translateIntToEncoding(string|int $encoding): string
    {
        if (is_string($encoding)) {
            return strtolower($encoding);
        }
        if (is_int($encoding)) {
            return match ($encoding) {
                Encoding::UTF16 => 'utf-16',
                Encoding::UTF16LE => 'utf-16le',
                Encoding::UTF16BE => 'utf-16be',
                Encoding::ISO88591 => 'iso-8859-1',
                default => 'utf-8',
            };
        }

        return 'utf-8';
    }

    /**
     * Backwards compatible alias for legacy underscore-prefixed helper.
     */
    final protected function _translateIntToEncoding(string|int $encoding): string
    {
        return $this->translateIntToEncoding($encoding);
    }

    /**
     * Returns given encoding in the form possible to write to the tag frame.
     * Character encoding set can be in the string form accepted by iconv or
     * already a {@see \Vollbehr\Media\Id3\Encoding} constant.
     */
    final protected function translateEncodingToInt(string|int $encoding): int
    {
        if (is_int($encoding) && ($encoding >= 0 && $encoding <= 4)) {
            return $encoding;
        }
        if (is_string($encoding)) {
            return match ($encoding) {
                'utf-16' => Encoding::UTF16,
                'utf-16le' => Encoding::UTF16,
                'utf-16be' => Encoding::UTF16BE,
                'iso-8859-1' => Encoding::ISO88591,
                default => Encoding::UTF8,
            };
        }

        return Encoding::UTF8;
    }

    /**
     * Backwards compatible alias for legacy underscore-prefixed helper.
     */
    final protected function _translateEncodingToInt(string|int $encoding): int
    {
        return $this->translateEncodingToInt($encoding);
    }

    /**
     * Writes the object data.
     */
    abstract public function write(Writer $writer): void;
}