<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * A base class for objects that also contain a version number and flags field.
 * @author Sven Vollbehr
 */
abstract class FullBox extends Box
{
    protected int $_version;

    protected int $_flags;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $this->_version = (($field = $this->_reader->readUInt32BE()) >> 24) & 0xff;
        $this->_flags   = $field & 0xffffff;
    }
    /**
     * Returns the version of this format of the box.
     * @return integer
     */
    public function getVersion()
    {
        return $this->_version;
    }
    /**
     * Sets the version of this format of the box.
     * @param integer $version The version.
     */
    public function setVersion(int $version): void
    {
        $this->_version = $version;
    }
    /**
     * Checks whether or not the flag is set. Returns <var>true</var> if the
     * flag is set, <var>false</var> otherwise.
     * @param integer $flag The flag to query.
     * @return boolean
     */
    public function hasFlag($flag)
    {
        return ($this->_flags & $flag) == $flag;
    }
    /**
     * Returns the map of flags.
     * @return integer
     */
    public function getFlags()
    {
        return $this->_flags;
    }
    /**
     * Sets the map of flags.
     * @param string $flags The map of flags.
     */
    public function setFlags(int $flags): void
    {
        $this->_flags = $flags;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() + 4;
    }
    /**
     * Writes the box data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    protected function _writeData($writer)
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($this->_version << 24 | $this->_flags);
    }
}
