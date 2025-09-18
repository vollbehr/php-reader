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
 * The _Decoding Time to Sample Box_ contains a compact version of a table
 * that allows indexing from decoding time to sample number. Other tables give
 * sample sizes and pointers, from the sample number. Each entry in the table
 * gives the number of consecutive samples with the same time delta, and the
 * delta of those samples. By adding the deltas a complete time-to-sample map
 * may be built.
 * The Decoding Time to Sample Box contains decode time delta's: DT(n+1) = DT(n)
 * + STTS(n) where STTS(n) is the (uncompressed) table entry for sample n.
 * The sample entries are ordered by decoding time stamps; therefore the deltas
 * are all non-negative.
 * The DT axis has a zero origin; DT(i) = SUM(for j=0 to i-1 of delta(j)), and
 * the sum of all deltas gives the length of the media in the track (not mapped
 * to the overall timescale, and not considering any edit list).
 * The {@see \Vollbehr\Media\Iso14496\Box\Elst Edit List Box} provides the initial
 * CT value if it is non-empty (non-zero).
 * @author Sven Vollbehr
 */
final class Stts extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_timeToSampleTable = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);

        $entryCount = $this->_reader->readUInt32BE();
        for ($i = 1; $i <= $entryCount; $i++) {
            $this->_timeToSampleTable[$i] = ['sampleCount' => $this->_reader->readUInt32BE(),
                 'sampleDelta' => $this->_reader->readUInt32BE()];
        }
    }

    /**
     * Returns an array of values. Each entry is an array containing the
     * following keys.
     *   o sampleCount -- an integer that counts the number of consecutive
     *     samples that have the given duration.
     *   o sampleDelta -- an integer that gives the delta of these samples in
     *     the time-scale of the media.
     * @return Array
     */
    public function getTimeToSampleTable()
    {
        return $this->_timeToSampleTable;
    }

    /**
     * Sets an array of values. Each entry must be an array containing the
     * following keys.
     *   o sampleCount -- an integer that counts the number of consecutive
     *     samples that have the given duration.
     *   o sampleDelta -- an integer that gives the delta of these samples in
     *     the time-scale of the media.
     * @param Array $timeToSampleTable The array of values.
     */
    public function setTimeToSampleTable($timeToSampleTable): void
    {
        $this->_timeToSampleTable = $timeToSampleTable;
    }

    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4 + count($this->_timeToSampleTable) * 8;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_timeToSampleTable));
        for ($i = 1; $i <= $entryCount; $i++) {
            $writer->writeUInt32BE($this->_timeToSampleTable[$i]['sampleCount'])
                   ->writeUInt32BE($this->_timeToSampleTable[$i]['sampleDelta']);
        }
    }
}
