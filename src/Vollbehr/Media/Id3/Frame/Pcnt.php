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
 * The _Play counter_ is simply a counter of the number of times a file has
 * been played. The value is increased by one every time the file begins to
 * play. There may only be one PCNT frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Pcnt extends \Vollbehr\Media\Id3\Frame
{
    /** @var integer */
    private $_counter = 0;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($this->_reader === null) {
            return;
        }

        if ($this->_reader->getSize() > 4) {
            $this->_counter = $this->_reader->readInt64BE(); // UInt64
        } else {
            $this->_counter = $this->_reader->readUInt32BE();
        }
    }

    /**
     * Returns the counter.
     * @return integer
     */
    public function getCounter()
    {
        return $this->_counter;
    }

    /**
     * Adds counter by one.
     */
    public function addCounter(): void
    {
        $this->_counter++;
    }

    /**
     * Sets the counter value.
     * @param integer $counter The counter value.
     */
    public function setCounter($counter): void
    {
        $this->_counter = $counter;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        if ($this->_counter > 4294967295) {
            $writer->writeInt64BE($this->_counter); // UInt64
        } else {
            $writer->writeUInt32BE($this->_counter);
        }
    }
}
