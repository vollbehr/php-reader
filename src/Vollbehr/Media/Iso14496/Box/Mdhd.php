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
 * The _Media Header Box_ declares overall information that is
 * media-independent, and relevant to characteristics of the media in a track.
 * @author Sven Vollbehr
 */
final class Mdhd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_creationTime;
    /** @var integer */
    private $_modificationTime;
    /** @var integer */
    private $_timescale;
    /** @var integer */
    private $_duration;
    private string $_language;

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
        $this->_language = chr(((($tmp = $this->_reader->readUInt16BE()) >> 10) & 0x1f) + 0x60) .
            chr((($tmp >> 5) & 0x1f) + 0x60) . chr(($tmp & 0x1f) + 0x60);
    }

    /**
     * Returns the creation time of the media in this track, in seconds since
     * midnight, Jan. 1, 1904, in UTC time.
     * @return integer
     */
    public function getCreationTime()
    {
        return $this->_creationTime;
    }
    /**
     * Sets the creation time of the media in this track, in seconds since
     * midnight, Jan. 1, 1904, in UTC time.
     * @param integer $creationTime The creation time.
     */
    public function setCreationTime($creationTime): void
    {
        $this->_creationTime = $creationTime;
    }
    /**
     * Returns the most recent time the media in this track was modified in
     * seconds since midnight, Jan. 1, 1904, in UTC time.
     * @return integer
     */
    public function getModificationTime()
    {
        return $this->_modificationTime;
    }
    /**
     * Sets the most recent time the media in this track was modified in
     * seconds since midnight, Jan. 1, 1904, in UTC time.
     * @param integer $modificationTime The modification time.
     */
    public function setModificationTime($modificationTime): void
    {
        $this->_modificationTime = $modificationTime;
    }
    /**
     * Returns the time-scale for this media. This is the number of time units
     * that pass in one second. For example, a time coordinate system that
     * measures time in sixtieths of a second has a time scale of 60.
     * @return integer
     */
    public function getTimescale()
    {
        return $this->_timescale;
    }
    /**
     * Sets the time-scale for this media. This is the number of time units
     * that pass in one second. For example, a time coordinate system that
     * measures time in sixtieths of a second has a time scale of 60.
     * @param integer $timescale The time-scale.
     */
    public function setTimescale($timescale): void
    {
        $this->_timescale = $timescale;
    }
    /**
     * Returns the duration of this media (in the scale of the timescale).
     * @return integer
     */
    public function getDuration()
    {
        return $this->_duration;
    }
    /**
     * Sets the duration of this media (in the scale of the timescale).
     * @param integer $duration The duration.
     */
    public function setDuration($duration): void
    {
        $this->_duration = $duration;
    }
    /**
     * Returns the three byte language code to describe the language of this
     * media, according to {@see http://www.loc.gov/standards/iso639-2/
     * ISO 639-2/T}.
     */
    public function getLanguage(): string
    {
        return $this->_language;
    }
    /**
     * Sets the three byte language code to describe the language of this
     * media, according to {@see http://www.loc.gov/standards/iso639-2/
     * ISO 639-2/T}.
     * @param string $language The language code.
     */
    public function setLanguage(string $language): void
    {
        $this->_language = $language;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() +
            ($this->getVersion() == 1 ? 28 : 16) + 4;
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
        $writer->writeUInt16BE((ord($this->_language[0]) - 0x60) << 10 |
                (ord($this->_language[1]) - 0x60) << 5 |
                 (ord($this->_language[2]) - 0x60))
               ->write(str_pad('', 2, "\0"));
    }
}
