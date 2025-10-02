<?php

declare(strict_types=1);

namespace Vollbehr\Media\Riff;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * This class represents the basic building block of a RIFF file, called a chunk.
 * @author Sven Vollbehr
 */
abstract class Chunk
{
    /**
     * The reader object.
     * @var Reader
     */
    protected $_reader;
    /** @var integer */
    protected $_identifier;

    /** @var integer */
    protected $_size;

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     */
    public function __construct($reader)
    {
        $this->_reader     = $reader;
        $this->_identifier = $this->_reader->read(4);
        $this->_size       = $this->_reader->readUInt32LE();
    }

    /**
     * Returns a four-character code that identifies the representation of the chunk data. A program reading a RIFF file
     * can skip over any chunk whose chunk ID it doesn't recognize; it simply skips the number of bytes specified by
     * size plus the pad byte, if present.
     * @return string
     */
    final public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Sets the four-character code that identifies the representation of the chunk data.
     * @param string $identifier The chunk identifier.
     */
    final public function setIdentifier($identifier): void
    {
        $this->_identifier = $identifier;
    }

    /**
     * Returns the size of chunk data. This size value does not include the size of the identifier or size fields or the
     * pad byte at the end of chunk data.
     * @return integer
     */
    final public function getSize()
    {
        return $this->_size;
    }

    /**
     * Sets the size of chunk data. This size value must not include the size of the identifier or size fields or the
     * pad byte at the end of chunk data.
     * @param integer $size The size of chunk data.
     */
    final public function setSize($size): void
    {
        $this->_size = $size;
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The field name.
     */
    public function __get(string $name)
    {
        if (method_exists($this, 'get' . ucfirst(strtolower($name)))) {
            return call_user_func([$this, 'get' . ucfirst(strtolower($name))]);
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
        if (method_exists($this, 'set' . ucfirst(strtolower($name)))) {
            call_user_func([$this, 'set' . ucfirst(strtolower($name))], $value);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }
}
