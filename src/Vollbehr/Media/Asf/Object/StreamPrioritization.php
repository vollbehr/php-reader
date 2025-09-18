<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf\Object;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Stream Prioritization Object_ indicates the author's intentions as
 * to which streams should or should not be dropped in response to varying
 * network congestion situations. There may be special cases where this
 * preferential order may be ignored (for example, the user hits the 'mute'
 * button). Generally it is expected that implementations will try to honor the
 * author's preference.
 * The priority of each stream is indicated by how early in the list that
 * stream's stream number is listed (in other words, the list is ordered in
 * terms of decreasing priority).
 * The Mandatory flag field shall be set if the author wants that stream kept
 * 'regardless'. If this flag is not set, then that indicates that the stream
 * should be dropped in response to network congestion situations. Non-mandatory
 * streams must never be assigned a higher priority than mandatory streams.
 * @author Sven Vollbehr
 */
final class StreamPrioritization extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var Array */
    private $_priorityRecords = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $priorityRecordCount = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $priorityRecordCount; $i++) {
            $this->_priorityRecords[] = ['streamNumber' => $this->_reader->readUInt16LE(),
                 'flags' => $this->_reader->readUInt16LE()];
        }
    }

    /**
     * Returns an array of records. Each record consists of the following keys.
     *   o streamNumber -- Specifies the stream number. Valid values are between
     *     1 and 127.
     *   o flags -- Specifies the flags. The mandatory flag is the bit 1 (LSB).
     * @return Array
     */
    public function getPriorityRecords()
    {
        return $this->_priorityRecords;
    }

    /**
     * Sets the array of records. Each record consists of the following keys.
     *   o streamNumber -- Specifies the stream number. Valid values are between
     *     1 and 127.
     *   o flags -- Specifies the flags. The mandatory flag is the bit 1 (LSB).
     * @param Array $priorityRecords The array of records.
     */
    public function setPriorityRecords($priorityRecords): void
    {
        $this->_priorityRecords = $priorityRecords;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $priorityRecordCount = count($this->_priorityRecords);
        $this->setSize(24 /* for header */ + 2 + $priorityRecordCount * 4);

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt16LE($priorityRecordCount);
        for ($i = 0; $i < $priorityRecordCount; $i++) {
            $writer->writeUInt16LE($this->_priorityRecords[$i]['streamNumber'])
                   ->writeUInt16LE($this->_priorityRecords[$i]['flags']);
        }
    }
}
