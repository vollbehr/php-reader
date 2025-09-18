<?php

declare(strict_types=1);

namespace Vollbehr\Media\Mpeg;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The base class for all MPEG objects.
 * @author Sven Vollbehr
 */
abstract class BaseObject
{
    /**
     * The reader object.
     * @var Reader
     */
    protected $_reader;
    /**
     * The options array.
     * @var Array
     */
    private $_options;
    /**
     * Constructs the class with given parameters.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        $this->_reader  = $reader;
        $this->_options = &$options;
    }

    /**
     * Returns the options array.
     * @return Array
     */
    final public function &getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns the given option value, or the default value if the option is not
     * defined.
     * @param string $option The name of the option.
     * @param mixed $defaultValue The default value to be returned.
     */
    final public function getOption($option, $defaultValue = null)
    {
        return $this->_options[$option] ?? $defaultValue;
    }

    /**
     * Sets the options array. See main class for available options.
     * @param Array $options The options array.
     */
    final public function setOptions(&$options): void
    {
        $this->_options = &$options;
    }

    /**
     * Sets the given option the given value.
     * @param string $option The name of the option.
     * @param mixed $value The value to set for the option.
     */
    final public function setOption($option, $value): void
    {
        $this->_options[$option] = $value;
    }

    /**
     * Clears the given option value.
     * @param string $option The name of the option.
     */
    final public function clearOption($option): void
    {
        unset($this->_options[$option]);
    }

    /**
     * Finds and returns the next start code. Start codes are reserved bit
     * patterns in the video file that do not otherwise occur in the video stream.
     * All start codes are byte aligned and start with the following byte
     * sequence: 0x00 0x00 0x01.
     * @return integer
     */
    final protected function nextStartCode()
    {
        $buffer = '    ';
        for ($i = 0; $i < 4; $i++) {
            $start = $this->_reader->getOffset();
            if (($buffer = substr($buffer, -4) .
                     $this->_reader->read(512)) === false) {

                throw new Exception('Invalid data');
            }
            $limit = strlen($buffer);
            $pos   = 0;
            while ($pos < $limit - 3) {
                if (ord($buffer[$pos++]) == 0) {
                    [, $int] = unpack('n*', substr($buffer, $pos, 2));
                    if ($int == 1) {
                        if (($pos += 2) < $limit - 2) {
                            [, $int] = unpack('n*', substr($buffer, $pos, 2));
                            if ($int == 0 && ord($buffer[$pos + 2]) == 1) {
                                continue;
                            }
                        }
                        $this->_reader->setOffset($start + $pos - 3);

                        return ord($buffer[$pos++]) & 0xff | 0x100;
                    }
                }
            }
            $this->_reader->setOffset($start + $limit);
        }

        /* No start code found within 2048 bytes, the maximum size of a pack */

        throw new Exception('Invalid data');
    }

    /**
     * Finds and returns the previous start code. Start codes are reserved bit
     * patterns in the video file that do not otherwise occur in the video
     * stream.
     * All start codes are byte aligned and start with the following byte
     * sequence: 0x00 0x00 0x01.
     * @return integer
     */
    final protected function prevStartCode()
    {
        $buffer   = '    ';
        $position = $this->_reader->getOffset();
        while ($position > 0) {
            $start = 0;
            $position -= 512;
            if ($position < 0) {

                throw new Exception('Invalid data');
            }
            $this->_reader->setOffset($position);
            $buffer = $this->_reader->read(512) . substr($buffer, 0, 4);
            $pos    = 512 - 8;
            while ($pos > 3) {
                [, $int] = unpack('n*', substr($buffer, $pos + 1, 2));
                if (ord($buffer[$pos]) == 0 && $int == 1) {
                    [, $int] = unpack('n*', substr($buffer, $pos + 3, 2));
                    if ($pos + 2 < 512 && $int == 0 &&
                            ord($buffer[$pos + 5]) == 1) {
                        $pos--;
                        continue;
                    }
                    $this->_reader->setOffset($position + $pos);

                    return ord($buffer[$pos + 3]) & 0xff | 0x100;
                }
                $pos--;
            }
            $this->_reader->setOffset($position += 3);
        }

        return 0;
    }

    /**
     * Formats given time in seconds into the form of
     * [hours:]minutes:seconds.milliseconds.
     * @param integer $seconds The time to format, in seconds
     * @return string
     */
    final protected function formatTime($seconds)
    {
        $milliseconds = round(($seconds - floor($seconds)) * 1000);
        $seconds      = floor($seconds);
        $minutes      = floor($seconds / 60);
        $hours        = floor($minutes / 60);

        return
            ($minutes > 0 ?
             ($hours > 0 ? $hours . ':' .
              str_pad($minutes % 60, 2, '0', STR_PAD_LEFT) : $minutes % 60) .
                ':' .
              str_pad($seconds % 60, 2, '0', STR_PAD_LEFT) : $seconds % 60) .
                '.' .
              str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The field name.
     */
    public function __get(string $name)
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
    public function __set(string $name, $value)
    {
        if (method_exists($this, 'set' . ucfirst($name))) {
            call_user_func([$this, 'set' . ucfirst($name)], $value);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }
}
