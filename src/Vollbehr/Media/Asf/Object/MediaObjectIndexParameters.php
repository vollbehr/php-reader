<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf\Object;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Media Object Index Parameters Object_ supplies information about
 * those streams that actually indexed (there must be at least one stream in an
 * index) by media objects. This object shall be present in the
 * {@see \Vollbehr\Media\Asf\BaseObject\Header Header Object} if there is a
 * {@see \Vollbehr\Media\Asf\BaseObject\MediaObjectIndex Media Object Index Object}
 * present in the file.
 * An Index Specifier is required for each stream that will be indexed by the
 * {@see \Vollbehr\Media\Asf\BaseObject\MediaObjectIndex Media Object Index Object}.
 * These specifiers must exactly match those in the
 * {@see \Vollbehr\Media\Asf\BaseObject\MediaObjectIndex Media Object Index Object}.
 * @author Sven Vollbehr
 */
final class MediaObjectIndexParameters extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var int */
    private $_indexEntryCountInterval;

    private array $_indexSpecifiers = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_indexEntryCountInterval = $this->_reader->readUInt32LE();
        $indexSpecifiersCount           = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $indexSpecifiersCount; $i++) {
            $this->_indexSpecifiers[] = ['streamNumber' => $this->_reader->readUInt16LE(),
                 'indexType' => $this->_reader->readUInt16LE()];
        }
    }

    /**
     * Returns the interval between each index entry by the number of media
     * objects. This value cannot be 0.
     * @return integer
     */
    public function getIndexEntryCountInterval()
    {
        return $this->_indexEntryCountInterval;
    }

    /**
     * Returns an array of index entries. Each entry consists of the following
     * keys.
     *   o streamNumber -- Specifies the stream number that the Index Specifiers
     *     refer to. Valid values are between 1 and 127.
     *   o indexType -- Specifies the type of index. Values are defined as
     *     follows:
     *       1 = Nearest Past Data Packet,
     *       2 = Nearest Past Media Object,
     *       3 = Nearest Past Cleanpoint,
     *       0xff = Frame Number Offset.
     *     For a video stream, the Nearest Past Media Object and Nearest Past
     *     Data Packet indexes point to the closest data packet containing an
     *     entire video frame or first fragment of a video frame; Nearest Past
     *     Cleanpoint indexes point to the closest data packet containing an
     *     entire video frame (or first fragment of a video frame) that is a key
     *     frame; and Frame Number Offset indicates how many more frames need to
     *     be read for the given stream, starting with the first frame in the
     *     packet pointed to by the index entry, in order to get to the
     *     requested frame. Nearest Past Media Object is the most common value.
     *     Because ASF payloads do not contain the full frame number, there is
     *     often a Frame Number Offset index alongside one of the other types of
     *     indexes to allow the user to identify the exact frame being seeked
     *     to.
     */
    public function getIndexSpecifiers(): array
    {
        return $this->_indexSpecifiers;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $indexSpecifiersCount = count($this->_indexSpecifiers);

        $this->setSize(24 /* for header */ + 4 + 2 + $indexSpecifiersCount * 4);

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt32LE((int) $this->_indexEntryCountInterval)
               ->writeUInt16LE($indexSpecifiersCount);

        foreach ($this->_indexSpecifiers as $specifier) {
            $writer->writeUInt16LE((int) ($specifier['streamNumber'] ?? 0))
                   ->writeUInt16LE((int) ($specifier['indexType'] ?? 0));
        }
    }
}
