<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Movie Fragment Header Box_ contains a sequence number, as a safety
 * check. The sequence number usually starts at 1 and must increase for each
 * movie fragment in the file, in the order in which they occur. This allows
 * readers to verify integrity of the sequence; it is an error to construct a
 * file where the fragments are out of sequence.
 * @author Sven Vollbehr
 */
final class Mfhd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_sequenceNumber;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_sequenceNumber = $this->_reader->readUInt32BE();
    }

    /**
     * Returns the ordinal number of this fragment, in increasing order.
     * @return integer
     */
    public function getSequenceNumber()
    {
        return $this->_sequenceNumber;
    }
    /**
     * Sets the ordinal number of this fragment, in increasing order.
     * @param integer $sequenceNumber The sequence number.
     */
    public function setSequenceNumber($sequenceNumber): void
    {
        $this->_sequenceNumber = $sequenceNumber;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($this->_sequenceNumber);
    }
}
