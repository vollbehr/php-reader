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
 * This top-level ASF object supplies media object indexing information for the
 * streams of an ASF file. It includes stream-specific indexing information
 * based on an adjustable index entry media object count interval. This object
 * can be used to index all the video frames or key frames in a video stream.
 * The index is designed to be broken into blocks to facilitate storage that is
 * more space-efficient by using 32-bit offsets relative to a 64-bit base. That
 * is, each index block has a full 64-bit offset in the block header that is
 * added to the 32-bit offset found in each index entry. If a file is larger
 * than 2^32 bytes, then multiple index blocks can be used to fully index the
 * entire large file while still keeping index entry offsets at 32 bits.
 * Indices into the _Media Object Index Object_ are in terms of media
 * object numbers, with the first frame for a given stream in the ASF file
 * corresponding to entry 0 in the _Media Object Index Object_. The
 * corresponding _Offset_ field values of the _Index Entry_ are byte
 * offsets that, when combined with the _Block Position_ value of the
 * Index Block, indicate the starting location in bytes of an ASF Data Packet
 * relative to the start of the first ASF Data Packet in the file.
 * Any ASF file containing a _Media Object Index Object_ shall also contain
 * a _Media Object Index Parameters Object_ in its
 * {@see \Vollbehr\Media\Asf\BaseObject\Header ASF Header}.
 * @author Sven Vollbehr
 */
final class MediaObjectIndex extends \Vollbehr\Media\Asf\BaseObject
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
    private $_indexEntryCountInterval;

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
        $this->_indexEntryCountInterval = $this->_reader->readUInt32LE();
        $indexSpecifiersCount           = $this->_reader->readUInt16LE();
        $indexBlocksCount               = $this->_reader->readUInt32LE();
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
     * Returns the interval between each index entry in number of media objects.
     * @return integer
     */
    public function getIndexEntryCountInterval()
    {
        return $this->_indexEntryCountInterval;
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
