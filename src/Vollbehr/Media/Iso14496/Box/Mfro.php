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
 * The _Movie Fragment Random Access Offset Box_ provides a copy of the
 * length field from the enclosing {@see \Vollbehr\Media\Iso14496\Box\MFRA Movie Fragment
 * Random Access Box}. It is placed last within that box, so that the size field
 * is also last in the enclosing Movie Fragment Random Access Box. When the
 * Movie Fragment Random Access Box is also last in the file this permits its
 * easy location. The size field here must be correct. However, neither the
 * presence of the Movie Fragment Random Access Box, nor its placement last in
 * the file, are assured.
 * @author Sven Vollbehr
 */
final class Mfro extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_parentSize;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_parentSize = $this->_reader->readUInt32BE();
    }

    /**
     * Returns the number of bytes of the enclosing
     * {@see \Vollbehr\Media\Iso14496\Box\Mfra} box. This field is placed at the
     * last of the enclosing box to assist readers scanning from the end of the
     * file in finding the _mfra_ box.
     * @return integer
     */
    public function getParentSize()
    {
        return $this->_parentSize;
    }
    /**
     * Sets the number of bytes of the enclosing
     * {@see \Vollbehr\Media\Iso14496\Box\Mfra} box. This field is placed at the
     * last of the enclosing box to assist readers scanning from the end of the
     * file in finding the _mfra_ box.
     * @param integer $parentSize The number of bytes.
     */
    public function setParentSize($parentSize): void
    {
        $this->_parentSize = $parentSize;
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
        $writer->writeUInt32BE($this->_parentSize);
    }
}
