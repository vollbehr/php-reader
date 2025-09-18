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
 * For each video stream in an ASF file, there should be one instance of the
 * _Simple Index Object_. Additionally, the instances of the _Simple
 * Index Object_ shall be ordered by stream number.
 * Index entries in the _Simple Index Object_ are in terms of
 * _Presentation Times_. The corresponding _Packet Number_ field
 * values (of the _Index Entry_, see below) indicate the packet number of
 * the ASF _Data Packet_ with the closest past key frame. Note that for
 * video streams that contain both key frames and non-key frames, the _Packet
 * Number_ field will always point to the closest past key frame.
 * @author Sven Vollbehr
 */
final class SimpleIndex extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
    private $_fileId;

    /** @var integer */
    private $_indexEntryTimeInterval;

    /** @var integer */
    private $_maximumPacketCount;

    private array $_indexEntries = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_fileId                 = $this->_reader->readGuid();
        $this->_indexEntryTimeInterval = $this->_reader->readInt64LE();
        $this->_maximumPacketCount     = $this->_reader->readUInt32LE();
        $indexEntriesCount             = $this->_reader->readUInt32LE();
        for ($i = 0; $i < $indexEntriesCount; $i++) {
            $this->_indexEntries[] = ['packetNumber' => $this->_reader->readUInt32LE(),
                 'packetCount' => $this->_reader->readUInt16LE()];
        }
    }

    /**
     * Returns the unique identifier for this ASF file. The value of this field
     * should be changed every time the file is modified in any way. The value
     * of this field may be set to 0 or set to be identical to the value of the
     * _File ID_ field of the _Data Object_ and the _Header
     * Object_.
     * @return string
     */
    public function getFileId()
    {
        return $this->_fileId;
    }

    /**
     * Returns the time interval between each index entry in 100-nanosecond units.
     * The most common value is 10000000, to indicate that the index entries are
     * in 1-second intervals, though other values can be used as well.
     * @return integer
     */
    public function getIndexEntryTimeInterval()
    {
        return $this->_indexEntryTimeInterval;
    }

    /**
     * Returns the maximum _Packet Count_ value of all _Index Entries_.
     * @return integer
     */
    public function getMaximumPacketCount()
    {
        return $this->_maximumPacketCount;
    }

    /**
     * Returns an array of index entries. Each entry consists of the following
     * keys.
     *   o packetNumber -- Specifies the number of the Data Packet associated
     *     with this index entry. Note that for video streams that contain both
     *     key frames and non-key frames, this field will always point to the
     *     closest key frame prior to the time interval.
     *   o packetCount -- Specifies the number of _Data Packets_ to send at
     *     this index entry. If a video key frame has been fragmented into two
     *     Data Packets, the value of this field will be equal to 2.
     */
    public function getIndexEntries(): array
    {
        return $this->_indexEntries;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): never
    {
        throw new \Vollbehr\Media\Asf\Exception('Operation not supported');
    }
}
