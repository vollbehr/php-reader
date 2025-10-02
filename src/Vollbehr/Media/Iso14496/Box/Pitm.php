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
 * For a given handler, the primary data may be one of the referenced items when
 * it is desired that it be stored elsewhere, or divided into extents; or the
 * primary metadata may be contained in the meta-box (e.g. in an
 * {@see \Vollbehr\Media\Iso14496\Box\Xml XML Box}). Either the _Primary Item
 * Box_ must occur, or there must be a box within the meta-box (e.g. an
 * {@see \Vollbehr\Media\Iso14496\Box\Xml XML Box}) containing the primary
 * information in the format required by the identified handler.
 * @author Sven Vollbehr
 */
final class Pitm extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var string */
    private $_itemId;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_itemId = $this->_reader->readUInt16BE();
    }

    /**
     * Returns the identifier of the primary item.
     * @return integer
     */
    public function getItemId()
    {
        return $this->_itemId;
    }
    /**
     * Sets the identifier of the primary item.
     * @param integer $itemId The item identification.
     */
    public function setItemId($itemId): void
    {
        $this->_itemId = $itemId;
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
        $writer->writeUInt16BE($this->_itemId);
    }
}
