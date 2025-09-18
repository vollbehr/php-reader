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
 * The _Hint Media Header Box_ header contains general information,
 * independent of the protocol, for hint tracks.
 * @author Sven Vollbehr
 */
final class Hmhd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_maxPDUSize;
    /** @var integer */
    private $_avgPDUSize;
    /** @var integer */
    private $_maxBitrate;
    /** @var integer */
    private $_avgBitrate;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_maxPDUSize = $this->_reader->readUInt16BE();
        $this->_avgPDUSize = $this->_reader->readUInt16BE();
        $this->_maxBitrate = $this->_reader->readUInt32BE();
        $this->_avgBitrate = $this->_reader->readUInt32BE();
    }
    /**
     * Returns the size in bytes of the largest PDU in this (hint) stream.
    *
     * @return integer
     */
    public function getMaxPDUSize()
    {
        return $this->_maxPDUSize;
    }
    /**
     * Returns the size in bytes of the largest PDU in this (hint) stream.
     * @param integer $maxPDUSize The maximum size.
     */
    public function setMaxPDUSize($maxPDUSize): void
    {
        $this->_maxPDUSize = $maxPDUSize;
    }
    /**
     * Returns the average size of a PDU over the entire presentation.
     * @return integer
     */
    public function getAvgPDUSize()
    {
        return $this->_avgPDUSize;
    }
    /**
     * Sets the average size of a PDU over the entire presentation.
     */
    public function setAvgPDUSize(): void
    {
        $this->_avgPDUSize = $avgPDUSize;
    }
    /**
     * Returns the maximum rate in bits/second over any window of one second.
     * @return integer
     */
    public function getMaxBitrate()
    {
        return $this->_maxBitrate;
    }
    /**
     * Sets the maximum rate in bits/second over any window of one second.
     * @param integer $maxBitrate The maximum bitrate.
     */
    public function setMaxBitrate($maxBitrate): void
    {
        $this->_maxBitrate = $maxBitrate;
    }
    /**
     * Returns the average rate in bits/second over the entire presentation.
     * @return integer
     */
    public function getAvgBitrate()
    {
        return $this->_avgBitrate;
    }
    /**
     * Sets the average rate in bits/second over the entire presentation.
     */
    public function setAvgBitrate($avgBitrate): void
    {
        $this->_avgBitrate = $avgBitrate;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 2;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt16BE($this->_maxPDUSize)
               ->writeUInt16BE($this->_avgPDUSize)
               ->writeUInt16BE($this->_maxBitrate)
               ->writeUInt16BE($this->_avgBitrate);
    }
}
