<?php

declare(strict_types=1);

namespace Vollbehr\Media\Riff;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents a chunk that contains a text string.
 * {{@internal The contained text string is a NULL-terminated string (ZSTR) that consists of a series of characters
 * followed by a terminating NULL character. The ZSTR is better than a simple character sequence (STR) because many
 * programs are easier to write if strings are NULL-terminated. ZSTR is preferred to a string with a size prefix (BSTR
 * or WSTR) because the size of the string is already available as the chunk size value, minus one for the terminating
 * NULL character.}}
 * @author Sven Vollbehr
 */
abstract class StringChunk extends Chunk
{
    protected string $_value;

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_value = rtrim((string) $this->_reader->read($this->_size), "\0");
    }

    /**
     * Returns the text string value.
     * @return string
     */
    final public function getValue()
    {
        return $this->_value;
    }

    /**
     * Sets the text string value.
     */
    final public function setValue(string $value): void
    {
        $this->_value = $value;
    }
}
