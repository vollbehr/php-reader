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
 * The _Item Information Box_ provides extra information about selected
 * items, including symbolic (_file_) names. It may optionally occur, but
 * if it does, it must be interpreted, as item protection or content encoding
 * may have changed the format of the data in the item. If both content encoding
 * and protection are indicated for an item, a reader should first un-protect
 * the item, and then decode the item's content encoding. If more control is
 * needed, an IPMP sequence code may be used.
 * @author Sven Vollbehr
 */
final class Iinf extends \Vollbehr\Media\Iso14496\Box
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

        $this->_reader->skip(2);
        $this->constructBoxes();
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
        $writer->writeUInt16BE($this->getBoxCount());
    }
}
