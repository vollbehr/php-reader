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
 * This top-level ASF object supplies the necessary indexing information for an
 * ASF file that contains more than just a plain script-audio-video combination.
 * It includes stream-specific indexing information based on an adjustable index
 * entry time interval. The index is designed to be broken into blocks to
 * facilitate storage that is more space-efficient by using 32-bit offsets
 * relative to a 64-bit base. That is, each index block has a full 64-bit offset
 * in the block header that is added to the 32-bit offsets found in each index
 * entry. If a file is larger than 2^32 bytes, then multiple index blocks can be
 * used to fully index the entire large file while still keeping index entry
 * offsets at 32 bits.
 * Indices into the _Index Object_ are in terms of presentation times. The
 * corresponding _Offset_ field values of the _Index Entry_ byte
 * offsets that, when combined with the _Block Position_ value of the
 * _Index Block_, indicate the starting location in bytes of an ASF Data
 * Packet relative to the start of the first ASF Data Packet in the file.
 * An offset value of 0xFFFFFFFF is used to indicate an invalid offset value.
 * Invalid offsets signify that this particular index entry does not identify a
 * valid indexible point. Invalid offsets may occur for the initial index
 * entries of a digital media stream whose first ASF Data Packet has a non-zero
 * send time. Invalid offsets may also occur in the case where a digital media
 * stream has a large gap in the presentation time of successive objects.
 * The _Index Object_ is not recommended for use with files where the
 * _Send Time_ of the first _Data Packet_ within the _Data
 * Object_ has a _Send Time_ value significantly greater than zero
 * (otherwise the index itself will be sparse and inefficient).
 * Any ASF file containing an _Index Object_ does also contain an _Index
 * Parameters Object_ in its {@see \Vollbehr\Media\Asf\BaseObject\Header ASF Header}.
 * @author Sven Vollbehr
 */
final class Index extends \Vollbehr\Media\Asf\BaseObject
{
    /**
     * Indicates that the index type is Nearest Past Data Packet. The Nearest
     * Past Data Packet indexes point to the data packet whose presentation time
     * is closest to the index entry time.
     */
    public const NEAREST_PAST_DATA_PACKET = 1;
    /**
     * Indicates that the index type is Nearest Past Media. The Nearest Past
     * Object indexes point to the closest data packet containing an entire
     * object or first fragment of an object.
     */
    public const NEAREST_PAST_MEDIA = 2;
    /**
     * Indicates that the index type is Nearest Past Cleanpoint. The Nearest
     * Past Cleanpoint indexes point to the closest data packet containing an
     * entire object (or first fragment of an object) that has the Cleanpoint
     * Flag set.
     * Nearest Past Cleanpoint is the most common type of index.
     */
    public const NEAREST_PAST_CLEANPOINT = 3;
    /** @var integer */
    private $_indexEntryTimeInterval;

    private array $_indexSpecifiers = [];

    private array $_indexBlocks = [];

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
        $indexBlocksCount              = $this->_reader->readUInt32LE();
        for ($i = 0; $i < $indexSpecifiersCount; $i++) {
            $this->_indexSpecifiers[] = ['streamNumber' => $this->_reader->readUInt16LE(),
                 'indexType' => $this->_reader->readUInt16LE()];
        }
        for ($i = 0; $i < $indexBlocksCount; $i++) {
            $indexEntryCount = $this->_reader->readUInt32LE();
            $blockPositions  = [];
            for ($i = 0; $i < $indexSpecifiersCount; $i++) {
                $blockPositions[] = $this->_reader->readInt64LE();
            }
            $offsets = [];
            for ($i = 0; $i < $indexSpecifiersCount; $i++) {
                $offsets[] = $this->_reader->readUInt32LE();
            }
            $this->_indexBlocks[] = ['blockPositions' => $blockPositions,
                 'indexEntryOffsets' => $offsets];
        }
    }

    /**
     * Returns the time interval between each index entry in ms.
     * @return integer
     */
    public function getIndexEntryTimeInterval()
    {
        return $this->_indexEntryTimeInterval;
    }

    /**
     * Returns an array of index specifiers. Each entry consists of the
     * following keys.
     *   o streamNumber -- Specifies the stream number that the _Index
     *     Specifiers_ refer to. Valid values are between 1 and 127.
     *   o indexType -- Specifies the type of index.
     */
    public function getIndexSpecifiers(): array
    {
        return $this->_indexSpecifiers;
    }

    /**
     * Returns an array of index entries. Each entry consists of the following
     * keys.
     *   o blockPositions -- Specifies a list of byte offsets of the beginnings
     *     of the blocks relative to the beginning of the first Data Packet (for
     *     example, the beginning of the Data Object + 50 bytes).
     *   o indexEntryOffsets -- Specifies the offset. An offset value of
     *     0xffffffff indicates an invalid offset value.
     */
    public function getIndexBlocks(): array
    {
        return $this->_indexBlocks;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): never
    {
        throw new \Vollbehr\Media\Asf\Exception('Operation not supported');
    }
}
