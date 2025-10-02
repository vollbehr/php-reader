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
 * The _Track Header Box_ specifies the characteristics of a single track.
 * Exactly one Track Header Box is contained in a track.
 * In the absence of an edit list, the presentation of a track starts at the
 * beginning of the overall presentation. An empty edit is used to offset the
 * start time of a track.
 * @author Sven Vollbehr
 */
final class Tkhd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_creationTime;
    /** @var integer */
    private $_modificationTime;
    /** @var integer */
    private $_trackId;
    /** @var integer */
    private $_duration;
    /** @var integer */
    private $_layer = 0;

    /** @var integer */
    private $_alternateGroup = 0;

    private float $_volume;

    private array $_matrix = [0x00010000, 0, 0, 0, 0x00010000, 0, 0, 0, 0x40000000];

    private float $_width;

    private float $_height;

    /**
     * Indicates that the track is enabled. A disabled track is treated as if it
     * were not present.
     */
    public const TRACK_ENABLED = 1;

    /**
     * Indicates that the track is used in the presentation.
     */
    public const TRACK_IN_MOVIE = 2;

    /**
     * Indicates that the track is used when previewing the presentation.
     */
    public const TRACK_IN_PREVIEW = 4;

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
            $this->_trackId          = $this->_reader->readUInt32BE();
            $this->_reader->skip(4);
            $this->_duration = $this->_reader->readInt64BE();
        } else {
            $this->_creationTime     = $this->_reader->readUInt32BE();
            $this->_modificationTime = $this->_reader->readUInt32BE();
            $this->_trackId          = $this->_reader->readUInt32BE();
            $this->_reader->skip(4);
            $this->_duration = $this->_reader->readUInt32BE();
        }
        $this->_reader->skip(8);
        $this->_layer          = $this->_reader->readInt16BE();
        $this->_alternateGroup = $this->_reader->readInt16BE();
        $this->_volume         = ((($tmp = $this->_reader->readUInt16BE()) >> 8) & 0xff) +
            (float)('0.' . ($tmp & 0xff));
        $this->_reader->skip(2);
        for ($i = 0; $i < 9; $i++) {
            $this->_matrix[$i] = $this->_reader->readUInt32BE();
        }
        $this->_width = ((($tmp = $this->_reader->readUInt32BE()) >> 16) & 0xffff) +
            (float)('0.' . ($tmp & 0xffff));
        $this->_height = ((($tmp = $this->_reader->readUInt32BE()) >> 16) & 0xffff) +
            (float)('0.' . ($tmp & 0xffff));
    }

    /**
     * Returns the creation time of this track in seconds since midnight, Jan. 1,
     * 1904, in UTC time.
     * @return integer
     */
    public function getCreationTime()
    {
        return $this->_creationTime;
    }
    /**
     * Sets the creation time of this track in seconds since midnight, Jan. 1,
     * 1904, in UTC time.
     */
    public function setCreationTime(): void
    {
        $this->_creationTime = $creationTime;
    }
    /**
     * Returns the most recent time the track was modified in seconds since
     * midnight, Jan. 1, 1904, in UTC time.
     * @return integer
     */
    public function getModificationTime()
    {
        return $this->_modificationTime;
    }
    /**
     * Sets the most recent time the track was modified in seconds since
     * midnight, Jan. 1, 1904, in UTC time.
     * @param integer $modificationTime The modification time.
     */
    public function setModificationTime($modificationTime): void
    {
        $this->_modificationTime = $modificationTime;
    }
    /**
     * Returns a number that uniquely identifies this track over the entire
     * life-time of this presentation. Track IDs are never re-used and cannot be
     * zero.
     * @return integer
     */
    public function getTrackId()
    {
        return $this->_trackId;
    }
    /**
     * Returns a number that uniquely identifies this track over the entire
     * life-time of this presentation. Track IDs are never re-used and cannot be
     * zero.
     * @param integer $trackId The track identification.
     */
    public function setTrackId($trackId): void
    {
        $this->_trackId = $trackId;
    }
    /**
     * Returns the duration of this track (in the timescale indicated in the
     * {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}). The value of this
     * field is equal to the sum of the durations of all of the track's edits.
     * If there is no edit list, then the duration is the sum of the sample
     * durations, converted into the timescale in the
     * {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}. If the duration
     * of this track cannot be determined then duration is set to all 32-bit
     * maxint.
     * @return integer
     */
    public function getDuration()
    {
        return $this->_duration;
    }
    /**
     * Sets the duration of this track (in the timescale indicated in the
     * {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}). The value of this
     * field must be equal to the sum of the durations of all of the track's
     * edits. If there is no edit list, then the duration must be the sum of the
     * sample durations, converted into the timescale in the
     * {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}. If the duration
     * of this track cannot be determined then duration is set to all 32-bit
     * maxint.
     * @param integer $duration The duration of this track.
     */
    public function setDuration($duration): void
    {
        $this->_duration = $duration;
    }
    /**
     * Returns the front-to-back ordering of video tracks; tracks with lower
     * numbers are closer to the viewer. 0 is the normal value, and -1 would be
     * in front of track 0, and so on.
    *
     * @return integer
     */
    public function getLayer()
    {
        return $this->_layer;
    }
    /**
     * Sets the front-to-back ordering of video tracks; tracks with lower
     * numbers are closer to the viewer. 0 is the normal value, and -1 would be
     * in front of track 0, and so on.
     * @param integer $layer The layer.
     */
    public function setLayer($layer): void
    {
        $this->_layer = $layer;
    }
    /**
     * Returns an integer that specifies a group or collection of tracks. If
     * this field is 0 there is no information on possible relations to other
     * tracks. If this field is not 0, it should be the same for tracks that
     * contain alternate data for one another and different for tracks belonging
     * to different such groups. Only one track within an alternate group
     * should be played or streamed at any one time, and must be distinguishable
     * from other tracks in the group via attributes such as bitrate, codec,
     * language, packet size etc. A group may have only one member.
     * @return integer
     */
    public function getAlternateGroup()
    {
        return $this->_alternateGroup;
    }
    /**
     * Returns an integer that specifies a group or collection of tracks. If
     * this field is 0 there is no information on possible relations to other
     * tracks. If this field is not 0, it should be the same for tracks that
     * contain alternate data for one another and different for tracks belonging
     * to different such groups. Only one track within an alternate group
     * should be played or streamed at any one time, and must be distinguishable
     * from other tracks in the group via attributes such as bitrate, codec,
     * language, packet size etc. A group may have only one member.
     * @param integer $alternateGroup The alternate group.
     */
    public function setAlternateGroup($alternateGroup): void
    {
        $this->_alternateGroup = $alternateGroup;
    }
    /**
     * Returns track's relative audio volume. Full volume is 1.0 (0x0100) and
     * is the normal value. Its value is irrelevant for a purely visual track.
     * Tracks may be composed by combining them according to their volume, and
     * then using the overall Movie Header Box volume setting; or more complex
     * audio composition (e.g. MPEG-4 BIFS) may be used.
     */
    public function getVolume(): float
    {
        return $this->_volume;
    }
    /**
     * Sets track's relative audio volume. Full volume is 1.0 (0x0100) and
     * is the normal value. Its value is irrelevant for a purely visual track.
     * Tracks may be composed by combining them according to their volume, and
     * then using the overall Movie Header Box volume setting; or more complex
     * audio composition (e.g. MPEG-4 BIFS) may be used.
     * @param integer $volume The volume.
     */
    public function setVolume(float $volume): void
    {
        $this->_volume = $volume;
    }
    /**
     * Returns the track's visual presentation width. This needs not be the same
     * as the pixel width of the images; all images in the sequence are scaled
     * to this width, before any overall transformation of the track represented
     * by the matrix. The pixel width of the images is the default value.
     */
    public function getWidth(): float
    {
        return $this->_width;
    }
    /**
     * Set the track's visual presentation width. This needs not be the same
     * as the pixel width of the images; all images in the sequence are scaled
     * to this width, before any overall transformation of the track represented
     * by the matrix. The pixel width of the images should be the default value.
     * @param integer $width The width.
     */
    public function setWidth(float $width): void
    {
        $this->_width = $width;
    }
    /**
     * Returns the track's visual presentation height. This needs not be the
     * same as the pixel height of the images; all images in the sequence are
     * scaled to this height, before any overall transformation of the track
     * represented by the matrix. The pixel height of the images is the default
     * value.
     */
    public function getHeight(): float
    {
        return $this->_height;
    }
    /**
     * Sets the track's visual presentation height. This needs not be the
     * same as the pixel height of the images; all images in the sequence are
     * scaled to this height, before any overall transformation of the track
     * represented by the matrix. The pixel height of the images should be the
     * default value.
     * @param integer $height The height.
     */
    public function setHeight(float $height): void
    {
        $this->_height = $height;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() +
            ($this->getVersion() == 1 ? 32 : 20) + 60;
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
                   ->writeUInt32BE($this->_trackId)
                   ->writeUInt32BE(0)
                   ->writeInt64BE($this->_duration);
        } else {
            $writer->writeUInt32BE($this->_creationTime)
                   ->writeUInt32BE($this->_modificationTime)
                   ->writeUInt32BE($this->_trackId)
                   ->writeUInt32BE(0)
                   ->writeUInt32BE($this->_duration);
        }

        @[, $volumeDecimals] = explode('.', $this->_volume);
        $writer->write(str_pad('', 8, "\0"))
               ->writeInt16BE($this->_layer)
               ->writeInt16BE($this->_alternateGroup)
               ->writeUInt16BE(floor($this->_volume) << 8 | $volumeDecimals)
               ->write(str_pad('', 2, "\0"));
        for ($i = 0; $i < 9; $i++) {
            $writer->writeUInt32BE($this->_matrix[$i]);
        }
        @[, $widthDecimals]  = explode('.', $this->_width);
        @[, $heightDecimals] = explode('.', $this->_height);
        $writer->writeUInt32BE(floor($this->_width) << 16 | $widthDecimals)
               ->writeUInt32BE(floor($this->_height) << 16 | $heightDecimals);
    }
}
