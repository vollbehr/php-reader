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
 * The _The Item Location Box_ provides a directory of resources in this or
 * other files, by locating their containing file, their offset within that
 * file, and their length. Placing this in binary format enables common handling
 * of this data, even by systems which do not understand the particular metadata
 * system (handler) used. For example, a system might integrate all the
 * externally referenced metadata resources into one file, re-adjusting file
 * offsets and file references accordingly.
 * Items may be stored fragmented into extents, e.g. to enable interleaving. An
 * extent is a contiguous subset of the bytes of the resource; the resource is
 * formed by concatenating the extents. If only one extent is used then either
 * or both of the offset and length may be implied:
 *   o If the offset is not identified (the field has a length of zero), then
 *     the beginning of the file (offset 0) is implied.
 *   o If the length is not specified, or specified as zero, then the entire
 *     file length is implied. References into the same file as this metadata,
 *     or items divided into more than one extent, should have an explicit
 *     offset and length, or use a MIME type requiring a different
 *     interpretation of the file, to avoid infinite recursion.
 * The size of the item is the sum of the extentLengths. Note: extents may be
 * interleaved with the chunks defined by the sample tables of tracks.
 * The dataReferenceIndex may take the value 0, indicating a reference into the
 * same file as this metadata, or an index into the dataReference table.
 * Some referenced data may itself use offset/length techniques to address
 * resources within it (e.g. an MP4 file might be included in this way).
 * Normally such offsets are relative to the beginning of the containing file.
 * The field base offset provides an additional offset for offset calculations
 * within that contained data. For example, if an MP4 file is included within a
 * file formatted to this specification, then normally data-offsets within that
 * MP4 section are relative to the beginning of file; baseOffset adds to those
 * offsets.
 * @author Sven Vollbehr
 */
final class Iloc extends \Vollbehr\Media\Iso14496\Box
{
    /** @var Array */
    private $_items = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);

        $offsetSize     = (($tmp = $this->_reader->readUInt16BE()) >> 12) & 0xf;
        $lengthSize     = ($tmp >> 8) & 0xf;
        $baseOffsetSize = ($tmp >> 4) & 0xf;
        $itemCount      = $this->_reader->readUInt16BE();
        for ($i = 0; $i < $itemCount; $i++) {
            $item                       = [];
            $item['itemId']             = $this->_reader->readUInt16BE();
            $item['dataReferenceIndex'] = $this->_reader->readUInt16BE();
            $item['baseOffset']         = ($baseOffsetSize == 4 ? $this->_reader->readUInt32BE() :
                 ($baseOffsetSize == 8 ? $this->_reader->readInt64BE() : 0));
            $extentCount     = $this->_reader->readUInt16BE();
            $item['extents'] = [];
            for ($j = 0; $j < $extentCount; $j++) {
                $extent           = [];
                $extent['offset'] = ($offsetSize == 4 ? $this->_reader->readUInt32BE() :
                     ($offsetSize == 8 ? $this->_reader->readInt64BE() : 0));
                $extent['length'] = ($lengthSize == 4 ? $this->_reader->readUInt32BE() :
                     ($lengthSize == 8 ? $this->_reader->readInt64BE() : 0));
                $item['extents'][] = $extent;
            }
            $this->_items[] = $item;
        }
    }

    /**
     * Returns the array of items. Each entry has the following keys set:
     * itemId, dataReferenceIndex, baseOffset, and extents.
     * @return Array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Sets the array of items. Each entry must have the following keys set:
     * itemId, dataReferenceIndex, baseOffset, and extents.
     */
    public function setItems($items): void
    {
        $this->_items = $items;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        $totalSize = 4;
        $counter   = count($this->_itemId);
        for ($i = 0; $i < $counter; $i++) {
            $totalSize += 6;
            if ($this->_itemId[$i]['baseOffset'] > 0xffffffff) {
                $totalSize += 8;
            } else {
                $totalSize += 4;
            }
            $extentCount = count($this->_itemId[$i]['extents']);
            for ($j = 0; $j < $extentCount; $j++) {
                if ($this->_itemId[$i]['extents'][$j]['offset'] > 0xffffffff) {
                    $totalSize += 8 * $extentCount;
                } else {
                    $totalSize += 4 * $extentCount;
                }
                if ($this->_itemId[$i]['extents'][$j]['length'] > 0xffffffff) {
                    $totalSize += 8 * $extentCount;
                } else {
                    $totalSize += 4 * $extentCount;
                }
            }
        }

        return parent::getHeapSize() + $totalSize;
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);

        $offsetSize     = 4;
        $lengthSize     = 4;
        $baseOffsetSize = 4;

        $itemCount = count($this->_itemId);
        for ($i = 0; $i < $itemCount; $i++) {
            if ($this->_itemId[$i]['baseOffset'] > 0xffffffff) {
                $baseOffsetSize = 8;
            }
            $counter = count($this->_itemId[$i]['extents']);
            for ($j = 0; $j < $counter; $j++) {
                if ($this->_itemId[$i]['extents'][$j]['offset'] > 0xffffffff) {
                    $offsetSize = 8;
                }
                if ($this->_itemId[$i]['extents'][$j]['length'] > 0xffffffff) {
                    $lengthSize = 8;
                }
            }
        }

        $writer->writeUInt16BE((($offsetSize & 0xf) << 12) | (($lengthSize & 0xf) << 8) |
             (($baseOffsetSize & 0xf) << 4))
               ->writeUInt16BE($itemCount);
        for ($i = 0; $i < $itemCount; $i++) {
            $writer->writeUInt16BE($this->_itemId[$i]['itemId'])
                   ->writeUInt16BE($this->_itemId[$i]['dataReferenceIndex']);
            if ($baseOffsetSize == 4) {
                $writer->writeUInt32BE($this->_itemId[$i]['baseOffset']);
            }
            if ($baseOffsetSize == 8) {
                $writer->writeInt64BE($this->_itemId[$i]['baseOffset']);
            }
            $writer->writeUInt16BE($extentCount = count($this->_itemId[$i]['extents']));
            for ($j = 0; $j < $extentCount; $j++) {
                if ($offsetSize == 4) {
                    $writer->writeUInt32BE($this->_itemId[$i]['extents'][$j]['offset']);
                }
                if ($offsetSize == 8) {
                    $writer->writeInt64BE($this->_itemId[$i]['extents'][$j]['offset']);
                }
                if ($offsetSize == 4) {
                    $writer->writeUInt32BE($this->_itemId[$i]['extents'][$j]['length']);
                }
                if ($offsetSize == 8) {
                    $writer->writeInt64BE($this->_itemId[$i]['extents'][$j]['length']);
                }
            }
        }
    }
}
