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
 * The _Chunk Offset Box_ table gives the index of each chunk into the
 * containing file. There are two variants, permitting the use of 32-bit or
 * 64-bit offsets. The latter is useful when managing very large presentations.
 * At most one of these variants will occur in any single instance of a sample
 * table.
 * Offsets are file offsets, not the offset into any box within the file (e.g.
 * {@see \Vollbehr\Media\Iso14496\Box\Mdat Media Data Box}). This permits referring
 * to media data in files without any box structure. It does also mean that care
 * must be taken when constructing a self-contained ISO file with its metadata
 * ({@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}) at the front, as the size of
 * the {@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box} will affect the chunk
 * offsets to the media data.
 * This box variant contains 32-bit offsets.
 * @author Sven Vollbehr
 */
final class Stco extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_chunkOffsetTable = [];
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
            $this->_chunkOffsetTable[$i] = $reader->readUInt32BE();
        }
    }

    /**
     * Returns an array of values. Each entry has the entry number as its index
     * and a 32 bit integer that gives the offset of the start of a chunk into
     * its containing media file as its value.
     * @return Array
     */
    public function getChunkOffsetTable()
    {
        return $this->_chunkOffsetTable;
    }

    /**
     * Sets an array of chunk offsets. Each entry must have the entry number as
     * its index and a 32 bit integer that gives the offset of the start of a
     * chunk into its containing media file as its value.
     * @param Array $chunkOffsetTable The chunk offset array.
     */
    public function setChunkOffsetTable($chunkOffsetTable): void
    {
        $this->_chunkOffsetTable = $chunkOffsetTable;
    }

    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4 + count($this->_chunkOffsetTable) * 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_chunkOffsetTable));
        for ($i = 1; $i <= $entryCount; $i++) {
            $writer->writeUInt32BE($this->_chunkOffsetTable[$i]);
        }
    }
}
