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
 * The _Movie Header Box_ defines overall information which is
 * media-independent, and relevant to the entire presentation considered as a
 * whole.
 * @author Sven Vollbehr
 */
final class Mvhd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_creationTime;
    /** @var integer */
    private $_modificationTime;
    /** @var integer */
    private $_timescale;
    /** @var integer */
    private $_duration;
    private float $_rate;

    private float $_volume;

    /** @var Array */
    private $_matrix = [0x00010000, 0, 0, 0, 0x00010000, 0, 0, 0, 0x40000000];

    /** @var integer */
    private $_nextTrackId;

    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($this->getVersion() == 1) {
            $this->_creationTime     = $this->_reader->readInt64BE();
            $this->_modificationTime = $this->_reader->readInt64BE();
            $this->_timescale        = $this->_reader->readUInt32BE();
            $this->_duration         = $this->_reader->readInt64BE();
        } else {
            $this->_creationTime     = $this->_reader->readUInt32BE();
            $this->_modificationTime = $this->_reader->readUInt32BE();
            $this->_timescale        = $this->_reader->readUInt32BE();
            $this->_duration         = $this->_reader->readUInt32BE();
        }
        $this->_rate = ((($tmp = $this->_reader->readUInt32BE()) >> 16) & 0xffff) +
            (float)('0.' . ($tmp & 0xffff));
        $this->_volume = ((($tmp = $this->_reader->readUInt16BE()) >> 8) & 0xff) +
            (float)('0.' . ($tmp & 0xff));
        $this->_reader->skip(10);
        for ($i = 0; $i < 9; $i++) {
            $this->_matrix[$i] = $this->_reader->readUInt32BE();
        }
        $this->_reader->skip(24);
        $this->_nextTrackId = $this->_reader->readUInt32BE();
    }

    /**
     * Returns the creation time of the presentation. The value is in seconds
     * since midnight, Jan. 1, 1904, in UTC time.
     * @return integer
     */
    public function getCreationTime()
    {
        return $this->_creationTime;
    }
    /**
     * Sets the creation time of the presentation in seconds since midnight,
     * Jan. 1, 1904, in UTC time.
     * @param integer $creationTime The creation time.
     */
    public function setCreationTime($creationTime): void
    {
        $this->_creationTime = $creationTime;
    }
    /**
     * Returns the most recent time the presentation was modified. The value is
     * in seconds since midnight, Jan. 1, 1904, in UTC time.
     * @return integer
     */
    public function getModificationTime()
    {
        return $this->_modificationTime;
    }
    /**
     * Sets the most recent time the presentation was modified in seconds since
     * midnight, Jan. 1, 1904, in UTC time.
     * @param integer $modificationTime The most recent time the presentation
     * was modified.
     */
    public function setModificationTime($modificationTime): void
    {
        $this->_modificationTime = $modificationTime;
    }
    /**
     * Returns the time-scale for the entire presentation. This is the number of
     * time units that pass in one second. For example, a time coordinate system
     * that measures time in sixtieths of a second has a time scale of 60.
     * @return integer
     */
    public function getTimescale()
    {
        return $this->_timescale;
    }
    /**
     * Sets the time-scale for the entire presentation. This is the number of
     * time units that pass in one second. For example, a time coordinate system
     * that measures time in sixtieths of a second has a time scale of 60.
     * @param integer $timescale The time-scale for the entire presentation.
     */
    public function setTimescale($timescale): void
    {
        $this->_timescale = $timescale;
    }
    /**
     * Returns the length of the presentation in the indicated timescale. This
     * property is derived from the presentation's tracks: the value of this
     * field corresponds to the duration of the longest track in the
     * presentation.
     * @return integer
     */
    public function getDuration()
    {
        return $this->_duration;
    }
    /**
     * Sets the length of the presentation in the indicated timescale. This
     * property must be derived from the presentation's tracks: the value of
     * this field must correspond to the duration of the longest track in the
     * presentation.
     * @param integer $duration The length of the presentation.
     */
    public function setDuration($duration): void
    {
        $this->_duration = $duration;
    }
    /**
     * Returns the preferred rate to play the presentation. 1.0 is normal
     * forward playback.
     */
    public function getRate(): float
    {
        return $this->_rate;
    }
    /**
     * Sets the preferred rate to play the presentation. 1.0 is normal
     * forward playback.
     * @param integer $rate The preferred play rate.
     */
    public function setRate(float $rate): void
    {
        $this->_rate = $rate;
    }
    /**
     * Returns the preferred playback volume. 1.0 is full volume.
     */
    public function getVolume(): float
    {
        return $this->_volume;
    }
    /**
     * Sets the preferred playback volume. 1.0 is full volume.
     * @param integer $volume The playback volume.
     */
    public function setVolume(float $volume): void
    {
        $this->_volume = $volume;
    }
    /**
     * Returns the transformation matrix for the video; (u,v,w) are restricted
     * here to (0,0,1), hex values (0,0,0x40000000).
     * @return Array
     */
    public function getMatrix()
    {
        return $this->_matrix;
    }
    /**
     * Sets the transformation matrix for the video; (u,v,w) are restricted
     * here to (0,0,1), hex values (0,0,0x40000000).
     * @param Array $matrix The transformation matrix array of 9 values
     */
    public function setMatrix($matrix): void
    {
        $this->_matrix = $matrix;
    }
    /**
     * Returns a value to use for the track ID of the next track to be added to
     * this presentation. Zero is not a valid track ID value. The value is
     * larger than the largest track-ID in use. If this value is equal to or
     * larger than 32-bit maxint, and a new media track is to be added, then a
     * search must be made in the file for a unused track identifier.
     * @return integer
     */
    public function getNextTrackId()
    {
        return $this->_nextTrackId;
    }
    /**
     * Sets a value to use for the track ID of the next track to be added to
     * this presentation. Zero is not a valid track ID value. The value must be
     * larger than the largest track-ID in use.
     * @param integer $nextTrackId The next track ID.
     */
    public function setNextTrackId($nextTrackId): void
    {
        $this->_nextTrackId = $nextTrackId;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() +
            ($this->getVersion() == 1 ? 28 : 16) + 80;
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        if ($this->getVersion() == 1) {
            $writer->writeInt64BE($this->_creationTime)
                   ->writeInt64BE($this->_modificationTime)
                   ->writeUInt32BE($this->_timescale)
                   ->writeInt64BE($this->_duration);
        } else {
            $writer->writeUInt32BE($this->_creationTime)
                   ->writeUInt32BE($this->_modificationTime)
                   ->writeUInt32BE($this->_timescale)
                   ->writeUInt32BE($this->_duration);
        }

        @[, $rateDecimals]   = explode('.', $this->_rate);
        @[, $volumeDecimals] = explode('.', $this->_volume);
        $writer->writeUInt32BE(floor($this->_rate) << 16 | $rateDecimals)
               ->writeUInt16BE(floor($this->_volume) << 8 | $volumeDecimals)
               ->write(str_pad('', 10, "\0"));
        for ($i = 0; $i < 9; $i++) {
            $writer->writeUInt32BE($this->_matrix[$i]);
        }
        $writer->write(str_pad('', 24, "\0"))
               ->writeUInt32BE($this->_nextTrackId);
    }
}
