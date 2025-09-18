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
 * The _Movie Extends Header Box_ is optional, and provides the overall
 * duration, including fragments, of a fragmented movie. If this box is not
 * present, the overall duration must be computed by examining each fragment.
 * @author Sven Vollbehr
 */
final class Mehd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_fragmentDuration;
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
        $this->_fragmentDuration = $this->getVersion() == 1 ? $this->_reader->readInt64BE() : $this->_reader->readUInt32BE();
    }

    /**
     * Returns the length of the presentation of the whole movie including
     * fragments (in the timescale indicated in the
     * {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}). The value of
     * this field corresponds to the duration of the longest track, including
     * movie fragments.
     * @return integer
     */
    public function getFragmentDuration()
    {
        return $this->_fragmentDuration;
    }
    /**
     * Sets the length of the presentation of the whole movie including
     * fragments (in the timescale indicated in the
     * {@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header Box}). The value of
     * this field must correspond to the duration of the longest track,
     * including movie fragments.
     * @param integer $fragmentDuration The fragment duration.
     */
    public function setFragmentDuration($fragmentDuration): void
    {
        $this->_fragmentDuration = $fragmentDuration;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + ($this->getVersion() == 1 ? 8 : 4);
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        if ($this->getVersion() == 1) {
            $writer->writeInt64BE($this->_fragmentDuration);
        } else {
            $writer->writeUInt32BE($this->_fragmentDuration);
        }
    }
}
