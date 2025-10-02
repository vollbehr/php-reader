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
 * The _Composition Time to Sample Box_ provides the offset between
 * decoding time and composition time. Since decoding time must be less than the
 * composition time, the offsets are expressed as unsigned numbers such that
 * CT(n) = DT(n) + CTTS(n) where CTTS(n) is the (uncompressed) table entry for
 * sample n.
 * The composition time to sample table is optional and must only be present if
 * DT and CT differ for any samples. Hint tracks do not use this box.
 * @author Sven Vollbehr
 */
final class Ctts extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_compositionOffsetTable = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $entryCount = $this->_reader->readUInt32BE();
        for ($i = 1; $i <= $entryCount; $i++) {
            $this->_compositionOffsetTable[$i] = ['sampleCount' => $this->_reader->readUInt32BE(),
                 'sampleOffset' => $this->_reader->readUInt32BE()];
        }
    }
    /**
     * Returns an array of values. Each entry is an array containing the
     * following keys.
     *   o sampleCount -- an integer that counts the number of consecutive
     *     samples that have the given offset.
     *   o sampleOffset -- a non-negative integer that gives the offset between
     *     CT and DT, such that CT(n) = DT(n) + CTTS(n).
     * @return Array
     */
    public function getCompositionOffsetTable()
    {
        return $this->_compositionOffsetTable;
    }
    /**
     * Sets an array of values. Each entry must have an array containing the
     * following keys.
     *   o sampleCount -- an integer that counts the number of consecutive
     *     samples that have the given offset.
     *   o sampleOffset -- a non-negative integer that gives the offset between
     *     CT and DT, such that CT(n) = DT(n) + CTTS(n).
     * @param Array $compositionOffsetTable The array of values.
     */
    public function setCompositionOffsetTable($compositionOffsetTable): void
    {
        $this->_compositionOffsetTable = $compositionOffsetTable;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4 +
            count($this->_compositionOffsetTable) * 8;
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_compositionOffsetTable));
        for ($i = 1; $i <= $entryCount; $i++) {
            $writer->writeUInt32BE($this->_compositionOffsetTable[$i]['sampleCount'])
                   ->writeUInt32BE($this->_compositionOffsetTable[$i]['sampleOffset']);
        }
    }
}
