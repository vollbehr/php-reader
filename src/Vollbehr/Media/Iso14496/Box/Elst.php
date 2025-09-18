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
 * The _Edit List Box_ contains an explicit timeline map. Each entry
 * defines part of the track time-line: by mapping part of the media time-line,
 * or by indicating empty time, or by defining a dwell, where a single
 * time-point in the media is held for a period.
 * @author Sven Vollbehr
 */
final class Elst extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_entries = [];
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
        for ($i = 0; $i < $entryCount; $i++) {
            $entry = [];
            if ($this->getVersion() == 1) {
                $entry['segmentDuration'] = $this->_reader->readInt64BE();
                $entry['mediaTime']       = $this->_reader->readInt64BE();
            } else {
                $entry['segmentDuration'] = $this->_reader->readUInt32BE();
                $entry['mediaTime']       = $this->_reader->readInt32BE();
            }
            $entry['mediaRate'] = (float)($this->_reader->readInt16BE() . '.' .
                    $this->_reader->readInt16BE());
            $this->_entries[] = $entry;
        }
    }

    /**
     * Returns an array of entries. Each entry is an array containing the
     * following keys.
     *   o segmentDuration: specifies the duration of this edit segment in units
     *     of the timescale in the
     *     {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}.
     *   o mediaTime: the starting time within the media of this edit segment
     *     (in media time scale units, in composition time). If this field is
     *     set to –1, it is an empty edit. The last edit in a track shall never
     *     be an empty edit. Any difference between the duration in the
     *     {@see \Vollbehr\Media\Iso14496\Box\MVHD Movie Header Box}, and the
     *     track's duration is expressed as an implicit empty edit at the end.
     *   o mediaRate: the relative rate at which to play the media corresponding
     *     to this edit segment. If this value is 0, then the edit is specifying
     *     a dwell: the media at media-time is presented for the
     *     segment-duration. Otherwise this field shall contain the value 1.
     * @return Array
     */
    public function getEntries()
    {
        return $this->_entries;
    }
    /**
     * Sets the array of entries. Each entry must be an array containing the
     * following keys.
     *   o segmentDuration: specifies the duration of this edit segment in units
     *     of the timescale in the
     *     {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}.
     *   o mediaTime: the starting time within the media of this edit segment
     *     (in media time scale units, in composition time). If this field is
     *     set to –1, it is an empty edit. The last edit in a track shall never
     *     be an empty edit. Any difference between the duration in the
     *     {@see \Vollbehr\Media\Iso14496\Box\MVHD Movie Header Box}, and the
     *     track's duration is expressed as an implicit empty edit at the end.
     *   o mediaRate: the relative rate at which to play the media corresponding
     *     to this edit segment. If this value is 0, then the edit is specifying
     *     a dwell: the media at media-time is presented for the
     *     segment-duration. Otherwise this field shall contain the value 1.
     * @param Array $entries The array of entries;
     */
    public function setEntries($entries): void
    {
        $this->_entries = $entries;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
            ($this->getVersion() == 1 ? 20 : 12);
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_entries));
        for ($i = 0; $i < $entryCount; $i++) {
            if ($this->getVersion() == 1) {
                $writer->writeInt64BE($this->_entries[$i]['segmentDuration'])
                       ->writeInt64BE($this->_entries[$i]['mediaTime']);
            } else {
                $writer->writeUInt32BE($this->_entries[$i]['segmentDuration'])
                       ->writeInt32BE($this->_entries[$i]['mediaTime']);
            }
            @[$mediaRateInteger, $mediaRateFraction] = explode('.', (float)$this->_entries[$i]['mediaRate']);
            $writer->writeInt16BE($mediaRateInteger)
                   ->writeInt16BE($mediaRateFraction);
        }
    }
}
