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
 * This box provides a reference from the containing track to another track in
 * the presentation. The referenced track(s) contain the original media for this
 * hint track.
 * @author Sven Vollbehr
 */
final class Hint extends \Vollbehr\Media\Iso14496\Box
{
    /** @var Array */
    private $_trackId = [];
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
        while ($this->_reader->getOffset <= $this->getSize()) {
            $this->_trackId[] = $this->_reader->readUInt32BE();
        }
    }

    /**
     * Returns an array of integer references from the containing track to
     * another track in the presentation. Track IDs are never re-used and cannot
     * be equal to zero.
    *
     * @return Array
     */
    public function getTrackId()
    {
        return $this->_trackId;
    }
    /**
     * Sets an array of integer references from the containing track to
     * another track in the presentation. Track IDs are never re-used and cannot
     * be equal to zero.
     * @param Array $trackId The array of values.
     */
    public function setTrackId($trackId): void
    {
        $this->_trackId = $trackId;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() + count($this->_trackId) * 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $counter = count($this->_trackId);
        for ($i = 0; $i < $counter; $i++) {
            $writer->writeUInt32BE($this->_trackId[$i]);
        }
    }
}
