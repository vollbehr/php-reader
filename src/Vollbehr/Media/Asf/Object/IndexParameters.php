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
 * The _Index Parameters Object_ supplies information about those streams
 * that are actually indexed (there must be at least one stream in an index) by
 * the {@see \Vollbehr\Media\Asf\BaseObject\Index Index Object} and how they are being
 * indexed. This object shall be present in the
 * {@see \Vollbehr\Media\Asf\BaseObject\Header Header Object} if there is an
 * {@see \Vollbehr\Media\Asf\BaseObject\Index Index Object} present in the file.
 * An Index Specifier is required for each stream that will be indexed by the
 * {@see \Vollbehr\Media\Asf\BaseObject\Index Index Object}. These specifiers must
 * exactly match those in the {@see \Vollbehr\Media\Asf\BaseObject\Index Index Object}.
 * @author Sven Vollbehr
 */
final class IndexParameters extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var int */
    private $_indexEntryTimeInterval;

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
        $this->_indexEntryTimeInterval = $this->_reader->readUInt32LE();
        $indexSpecifiersCount          = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $indexSpecifiersCount; $i++) {
            $this->_indexSpecifiers[] = ['streamNumber' => $this->_reader->readUInt16LE(),
                 'indexType' => $this->_reader->readUInt16LE()];
        }
    }

    /**
     * Returns the time interval between index entries in milliseconds. This
     * value cannot be 0.
     * @return integer
     */
    public function getIndexEntryTimeInterval()
    {
        return $this->_indexEntryTimeInterval;
    }

    /**
     * Returns an array of index entries. Each entry consists of the following
     * keys.
     *   o streamNumber -- Specifies the stream number that the Index Specifiers
     *     refer to. Valid values are between 1 and 127.
     *   o indexType -- Specifies the type of index. Values are as follows:
     *       1 = Nearest Past Data Packet,
     *       2 = Nearest Past Media Object, and
     *       3 = Nearest Past Cleanpoint.
     *     The Nearest Past Data Packet indexes point to the data packet whose
     *     presentation time is closest to the index entry time. The Nearest
     *     Past Object indexes point to the closest data packet containing an
     *     entire object or first fragment of an object. The Nearest Past
     *     Cleanpoint indexes point to the closest data packet containing an
     *     entire object (or first fragment of an object) that has the
     *     Cleanpoint Flag set. Nearest Past Cleanpoint is the most common type
     *     of index.
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
               ->writeUInt32LE((int) $this->_indexEntryTimeInterval)
               ->writeUInt16LE($indexSpecifiersCount);

        foreach ($this->_indexSpecifiers as $specifier) {
            $writer->writeUInt16LE((int) ($specifier['streamNumber'] ?? 0))
                   ->writeUInt16LE((int) ($specifier['indexType'] ?? 0));
        }
    }
}
