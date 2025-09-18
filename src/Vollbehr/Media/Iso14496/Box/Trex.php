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
 * The _Track Extends Box_ sets up default values used by the movie
 * fragments. By setting defaults in this way, space and complexity can be saved
 * in each {@see \Vollbehr\Media\Iso14496\Box\Traf Track Fragment Box}.
 * @author Sven Vollbehr
 */
final class Trex extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_trackId;
    /** @var integer */
    private $_defaultSampleDescriptionIndex;
    /** @var integer */
    private $_defaultSampleDuration;
    /** @var integer */
    private $_defaultSampleSize;
    /** @var integer */
    private $_defaultSampleFlags;

    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     * @todo  The sample flags could be parsed further
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_trackId                       = $this->_reader->readUInt32BE();
        $this->_defaultSampleDescriptionIndex = $this->_reader->readUInt32BE();
        $this->_defaultSampleDuration         = $this->_reader->readUInt32BE();
        $this->_defaultSampleSize             = $this->_reader->readUInt32BE();
        $this->_defaultSampleFlags            = $this->_reader->readUInt32BE();
    }
    /**
     * Returns the default track identifier.
    *
     * @return integer
     */
    public function getTrackId()
    {
        return $this->_trackId;
    }
    /**
     * Sets the default track identifier.
     * @param integer $trackId The track identifier.
     */
    public function setTrackId($trackId): void
    {
        $this->_trackId = $trackId;
    }
    /**
     * Returns the default sample description index.
     * @return integer
     */
    public function getDefaultSampleDescriptionIndex()
    {
        return $this->_defaultSampleDescriptionIndex;
    }
    /**
     * Sets the default sample description index.
     * @param integer $defaultSampleDescriptionIndex The description index.
     */
    public function setDefaultSampleDescriptionIndex($defaultSampleDescriptionIndex): void
    {
        $this->_defaultSampleDescriptionIndex = $defaultSampleDescriptionIndex;
    }
    /**
     * Returns the default sample duration.
     * @return integer
     */
    public function getDefaultSampleDuration()
    {
        return $this->_defaultSampleDuration;
    }
    /**
     * Sets the default sample duration.
     * @param integer $defaultSampleDuration The sample duration.
     */
    public function setDefaultSampleDuration($defaultSampleDuration): void
    {
        $this->_defaultSampleDuration = $defaultSampleDuration;
    }
    /**
     * Returns the default sample size.
     * @return integer
     */
    public function getDefaultSampleSize()
    {
        return $this->_defaultSampleSize;
    }
    /**
     * Sets the default sample size.
     * @param integer $defaultSampleSize The sample size.
     */
    public function setDefaultSampleSize($defaultSampleSize): void
    {
        $this->_defaultSampleSize = $defaultSampleSize;
    }
    /**
     * Returns the default sample flags.
     * @return integer
     */
    public function getDefaultSampleFlags()
    {
        return $this->_defaultSampleFlags;
    }
    /**
     * Sets the default sample flags.
     */
    public function setDefaultSampleFlags(): void
    {
        $this->_defaultSampleFlags = $defaultSampleFlags;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 20;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($this->_trackId)
               ->writeUInt32BE($this->_defaultSampleDescriptionIndex)
               ->writeUInt32BE($this->_defaultSampleDuration)
               ->writeUInt32BE($this->_defaultSampleSize)
               ->writeUInt32BE($this->_defaultSampleFlags);
    }
}
