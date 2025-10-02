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
 * Samples within the media data are grouped into chunks. Chunks can be of
 * different sizes, and the samples within a chunk can have different sizes.
 * The _Sample To Chunk Box_ table can be used to find the chunk that
 * contains a sample, its position, and the associated sample description.
 * The table is compactly coded. Each entry gives the index of the first chunk
 * of a run of chunks with the same characteristics. By subtracting one entry
 * here from the previous one, you can compute how many chunks are in this run.
 * You can convert this to a sample count by multiplying by the appropriate
 * samplesPerChunk.
 * @author Sven Vollbehr
 */
final class Stsc extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_sampleToChunkTable = [];
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
            $this->_sampleToChunkTable[$i] = ['firstChunk' => $this->_reader->readUInt32BE(),
                 'samplesPerChunk' => $this->_reader->readUInt32BE(),
                 'sampleDescriptionIndex' => $this->_reader->readUInt32BE()];
        }
    }
    /**
     * Returns an array of values. Each entry is an array containing the
     * following keys.
     *   o firstChunk -- an integer that gives the index of the first chunk in
     *     this run of chunks that share the same samplesPerChunk and
     *     sampleDescriptionIndex; the index of the first chunk in a track has
     *     the value 1 (the firstChunk field in the first record of this box
     *     has the value 1, identifying that the first sample maps to the first
     *     chunk).
     *   o samplesPerChunk is an integer that gives the number of samples in
     *     each of these chunks.
     *   o sampleDescriptionIndex is an integer that gives the index of the
     *     sample entry that describes the samples in this chunk. The index
     *     ranges from 1 to the number of sample entries in the
     *     {@see \Vollbehr\Media\Iso14496\Box\Stsd Sample Description Box}.
     * @return Array
     */
    public function getSampleToChunkTable()
    {
        return $this->_sampleToChunkTable;
    }
    /**
     * Sets an array of values. Each entry is an array containing the
     * following keys.
     *   o firstChunk -- an integer that gives the index of the first chunk in
     *     this run of chunks that share the same samplesPerChunk and
     *     sampleDescriptionIndex; the index of the first chunk in a track has
     *     the value 1 (the firstChunk field in the first record of this box
     *     has the value 1, identifying that the first sample maps to the first
     *     chunk).
     *   o samplesPerChunk is an integer that gives the number of samples in
     *     each of these chunks.
     *   o sampleDescriptionIndex is an integer that gives the index of the
     *     sample entry that describes the samples in this chunk. The index
     *     ranges from 1 to the number of sample entries in the
     *     {@see \Vollbehr\Media\Iso14496\Box\Stsd Sample Description Box}.
     * @param Array $sampleToChunkTable The array of values.
     */
    public function setSampleToChunkTable($sampleToChunkTable): void
    {
        $this->_sampleToChunkTable = $sampleToChunkTable;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4 +
            count($this->_sampleToChunkTable) * 12;
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($entryCount = count($this->_sampleToChunkTable));
        for ($i = 1; $i <= $entryCount; $i++) {
            $writer->writeUInt32BE($this->_sampleToChunkTable[$i]['firstChunk'])
                   ->writeUInt32BE($this->_sampleToChunkTable[$i]['samplesPerChunk'])
                   ->writeUInt32BE($this->_sampleToChunkTable[$i]
                            ['sampleDescriptionIndex']);
        }
    }
}