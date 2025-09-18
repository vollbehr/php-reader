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
 * The _Progressive Download Information Box_ aids the progressive download
 * of an ISO file. The box contains pairs of numbers (to the end of the box)
 * specifying combinations of effective file download bitrate in units of
 * bytes/sec and a suggested initial playback delay in units of milliseconds.
 * A receiving party can estimate the download rate it is experiencing, and from
 * that obtain an upper estimate for a suitable initial delay by linear
 * interpolation between pairs, or by extrapolation from the first or last
 * entry.
 * @author Sven Vollbehr
 */
final class Pdin extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_progressiveDownloadInfo = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        while ($this->_reader->getOffset() <
               $this->getOffset() + $this->getSize()) {
            $this->_progressiveDownloadInfo[] = ['rate' => $this->_reader->readUInt32BE(),
                 'initialDelay' => $this->_reader->readUInt32BE()];
        }
    }
    /**
     * Returns the progressive download information array. The array consists of
     * items having two keys.
     *   o rate  --  the download rate expressed in bytes/second
     *   o initialDelay  --  the suggested delay to use when playing the file,
     *     such that if download continues at the given rate, all data within
     *     the file will arrive in time for its use and playback should not need
     *     to stall.
     * @return Array
     */
    public function getProgressiveDownloadInfo()
    {
        return $this->_progressiveDownloadInfo;
    }
    /**
     * Sets the progressive download information array. The array must consist
     * of items having two keys.
    *
     *   o rate  --  the download rate expressed in bytes/second
     *   o initialDelay  --  the suggested delay to use when playing the file,
     *     such that if download continues at the given rate, all data within
     *     the file will arrive in time for its use and playback should not need
     *     to stall.
     * @param Array $progressiveDownloadInfo The array of values.
     */
    public function setProgressiveDownloadInfo($progressiveDownloadInfo): void
    {
        $this->_progressiveDownloadInfo = $progressiveDownloadInfo;
    }
    /**
     * Returns the box heap size in bytes.
    *
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() +
            count($this->_progressiveDownloadInfo) * 8;
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $counter = count($this->_timeToSampleTable);
        for ($i = 0; $i < $counter; $i++) {
            $writer->writeUInt32BE($this->_progressiveDownloadInfo[$i]['rate'])
                   ->writeUInt32BE($this->_progressiveDownloadInfo[$i]['initialDelay']);
        }
    }
}
