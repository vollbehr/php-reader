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
 * The _Sync Sample Box_ provides a compact marking of the random access
 * points within the stream. The table is arranged in strictly increasing order
 * of sample number. If the sync sample box is not present, every sample is a
 * random access point.
 * @author Sven Vollbehr
 */
final class Stss extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_syncSampleTable = [];
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
        $entryCount = $this->_reader->readUInt32BE();
        for ($i = 1; $i <= $entryCount; $i++) {
            $this->_syncSampleTable[$i] = $this->_reader->readUInt32BE();
        }
    }
    /**
     * Returns an array of values. Each entry has the entry number as its index
     * and an integer that gives the numbers of the samples that are random
     * access points in the stream as its value.
     * @return Array
     */
    public function getSyncSampleTable()
    {
        return $this->_syncSampleTable;
    }
    /**
     * Sets an array of values. Each entry has the entry number as its index
     * and an integer that gives the numbers of the samples that are random
     * access points in the stream as its value.
     * @param Array $syncSampleTable The array of values.
     */
    public function setSyncSampleTable($syncSampleTable): void
    {
        $this->_syncSampleTable = $syncSampleTable;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4 + count($this->_syncSampleTable) * 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_syncSampleTable));
        for ($i = 1; $i <= $entryCount; $i++) {
            $writer->writeUInt32BE($this->_syncSampleTable[$i]);
        }
    }
}
