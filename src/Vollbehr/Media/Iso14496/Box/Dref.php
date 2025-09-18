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
 * The _Data Reference Box_ contains a table of data references (normally
 * URLs) that declare the location(s) of the media data used within the
 * presentation. The data reference index in the sample description ties entries
 * in this table to the samples in the track. A track may be split over several
 * sources in this way.
 * This box may either contain {@see \Vollbehr\Media\Iso14496\Box\Urn urn} or
 * {@see \Vollbehr\Media\Iso14496\Box\Url url} boxes.
 * @author Sven Vollbehr
 */
final class Dref extends \Vollbehr\Media\Iso14496\FullBox
{
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setContainer(true);
        if ($reader === null) {
            return;
        }

        $this->_reader->skip(4);
        $this->constructBoxes();
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($this->getBoxCount());
    }
}
