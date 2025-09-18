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
 * The _Shadow Sync Sample Box_ table provides an optional set of sync
 * samples that can be used when seeking or for similar purposes. In normal
 * forward play they are ignored.
 * Each entry in the Shadow Sync Table consists of a pair of sample numbers. The
 * first entry (shadowedSampleNumber) indicates the number of the sample that a
 * shadow sync will be defined for. This should always be a non-sync sample
 * (e.g. a frame difference). The second sample number (syncSampleNumber)
 * indicates the sample number of the sync sample (i.e. key frame) that can be
 * used when there is a random access at, or before, the shadowedSampleNumber.
 * The shadow sync samples are normally placed in an area of the track that is
 * not presented during normal play (edited out by means of an edit list),
 * though this is not a requirement. The shadow sync table can be ignored and
 * the track will play (and seek) correctly if it is ignored (though perhaps not
 * optimally).
 * The Shadow Sync Sample replaces, not augments, the sample that it shadows
 * (i.e. the next sample sent is shadowedSampleNumber+1). The shadow sync sample
 * is treated as if it occurred at the time of the sample it shadows, having the
 * duration of the sample it shadows.
 * Hinting and transmission might become more complex if a shadow sample is used
 * also as part of normal playback, or is used more than once as a shadow. In
 * this case the hint track might need separate shadow syncs, all of which can
 * get their media data from the one shadow sync in the media track, to allow
 * for the different time-stamps etc. needed in their headers.
 * @author Sven Vollbehr
 */
final class Stsh extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_shadowSyncSampleTable = [];
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
        for ($i = 0; $i < $entryCount; $i++) {
            $this->_shadowSyncSampleTable[$i] = ['shadowedSampleNumber' => $this->_reader->readUInt32BE(),
                 'syncSampleNumber' => $this->_reader->readUInt32BE()];
        }
    }

    /**
     * Returns an array of values. Each entry is an array containing the
     * following keys.
     *   o shadowedSampleNumber - gives the number of a sample for which there
     *     is an alternative sync sample.
     *   o syncSampleNumber - gives the number of the alternative sync sample.
     * @return Array
     */
    public function getShadowSyncSampleTable()
    {
        return $this->_shadowSyncSampleTable;
    }

    /**
     * Sets an array of values. Each entry must be an array containing the
     * following keys.
     *   o shadowedSampleNumber - gives the number of a sample for which there
     *     is an alternative sync sample.
     *   o syncSampleNumber - gives the number of the alternative sync sample.
     * @param Array $shadowSyncSampleTable The array of values.
     */
    public function setShadowSyncSampleTable($shadowSyncSampleTable): void
    {
        $this->_shadowSyncSampleTable = $shadowSyncSampleTable;
    }

    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4 +
            count($this->_shadowSyncSampleTable) * 8;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_shadowSyncSampleTable));
        for ($i = 1; $i <= $entryCount; $i++) {
            $writer->writeUInt32BE($this->_shadowSyncSampleTable[$i]
                            ['shadowedSampleNumber'])
                   ->writeUInt32BE($this->_shadowSyncSampleTable[$i]['syncSampleNumber']);
        }
    }
}
