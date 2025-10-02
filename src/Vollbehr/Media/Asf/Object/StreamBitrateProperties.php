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
 * The _Stream Bitrate Properties Object_ defines the average bit rate of
 * each digital media stream.
 * @author Sven Vollbehr
 */
final class StreamBitrateProperties extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var Array */
    private $_bitrateRecords = [];

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

        $bitrateRecordsCount = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $bitrateRecordsCount; $i++) {
            $this->_bitrateRecords[] = ['streamNumber' =>
                     ($tmp = $this->_reader->readInt16LE()) & 0x1f,
                 'flags' => $tmp >> 5,
                 'averageBitrate' => $this->_reader->readUInt32LE()];
        }
    }

    /**
     * Returns an array of bitrate records. Each record consists of the
     * following keys.
     *   o streamNumber -- Specifies the number of this stream described by this
     *     record. 0 is an invalid stream. Valid values are between 1 and 127.
     *   o flags -- These bits are reserved and should be set to 0.
     *   o averageBitrate -- Specifies the average bit rate of the stream in
     *     bits per second. This value should include an estimate of ASF packet
     *     and payload overhead associated with this stream.
     * @return Array
     */
    public function getBitrateRecords()
    {
        return $this->_bitrateRecords;
    }

    /**
     * Sets an array of bitrate records. Each record consists of the following
     * keys.
     *   o streamNumber -- Specifies the number of this stream described by this
     *     record. 0 is an invalid stream. Valid values are between 1 and 127.
     *   o flags -- These bits are reserved and should be set to 0.
     *   o averageBitrate -- Specifies the average bit rate of the stream in bits
     *     per second. This value should include an estimate of ASF packet and
     *     payload overhead associated with this stream.
     * @param Array $bitrateRecords The array of bitrate records.
     */
    public function setBitrateRecords($bitrateRecords): void
    {
        $this->_bitrateRecords = $bitrateRecords;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $bitrateRecordsCount = count($this->_bitrateRecords);
        $this->setSize(24 /* for header */ + 2 + $bitrateRecordsCount * 6);

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt16LE($bitrateRecordsCount);
        for ($i = 0; $i < $bitrateRecordsCount; $i++) {
            $writer->writeUInt16LE(($this->_bitrateRecords[$i]['flags'] << 5) |
                         ($this->_bitrateRecords[$i]['streamNumber'] & 0x1f))
                   ->writeUInt32LE($this->_bitrateRecords[$i]['averageBitrate']);
        }
    }
}
