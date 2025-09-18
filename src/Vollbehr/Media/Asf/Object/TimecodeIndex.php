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
 * This top-level ASF object supplies timecode indexing information for the
 * streams of an ASF file. It includes stream-specific indexing information
 * based on the timecodes found in the file. If the _Timecode Index Object_
 * is used, it is recommended that timecodes be stored as a _Payload Extension
 * System_ on the appropriate stream. It is also recommended that every
 * timecode appearing in the ASF file have a corresponging index entry.
 * The index is designed to be broken into blocks to facilitate storage that is
 * more space-efficient by using 32-bit offsets relative to a 64-bit base. That
 * is, each index block has a full 64-bit offset in the block header that is
 * added to the 32-bit offsets found in each index entry. If a file is larger
 * than 2^32 bytes, then multiple index blocks can be used to fully index the
 * entire large file while still keeping index entry offsets at 32 bits.
 * To locate an object with a particular timecode in an ASF file, one would
 * typically look through the _Timecode Index Object_ in blocks of the
 * appropriate range and try to locate the nearest possible timecode. The
 * corresponding _Offset_ field values of the _Index Entry_ are byte
 * offsets that, when combined with the _Block Position_ value of the Index
 * Block, indicate the starting location in bytes of an ASF Data Packet relative
 * to the start of the first ASF Data Packet in the file.
 * Any ASF file containing a _Timecode Index Object_ shall also contain a
 * _Timecode Index Parameters Object_ in its
 * {@see \Vollbehr\Media\Asf\BaseObject\Header ASF Header}.
 * @author Sven Vollbehr
 */
final class TimecodeIndex extends \Vollbehr\Media\Asf\BaseObject
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
        $this->_reader->skip(4);
        $indexSpecifiersCount = $this->_reader->readUInt16LE();
        $indexBlocksCount     = $this->_reader->readUInt32LE();
        for ($i = 0; $i < $indexSpecifiersCount; $i++) {
            $this->_indexSpecifiers[] = ['streamNumber' => $this->_reader->readUInt16LE(),
                 'indexType' => $this->_reader->readUInt16LE()];
        }
        for ($i = 0; $i < $indexBlocksCount; $i++) {
            $indexEntryCount = $this->_reader->readUInt32LE();
            $timecodeRange   = $this->_reader->readUInt16LE();
            $blockPositions  = [];
            for ($i = 0; $i < $indexSpecifiersCount; $i++) {
                $blockPositions[] = $this->_reader->readInt64LE();
            }
            $indexEntries = [];
            for ($i = 0; $i < $indexEntryCount; $i++) {
                $timecode = $this->_reader->readUInt32LE();
                $offsets  = [];
                for ($i = 0; $i < $indexSpecifiersCount; $i++) {
                    $offsets[] = $this->_reader->readUInt32LE();
                }
                $indexEntries[] = ['timecode' => $timecode,
                     'offsets' => $offsets];
            }
            $this->_indexBlocks[] = ['timecodeRange' => $timecodeRange,
                 'blockPositions' => $blockPositions,
                 'indexEntries' => $indexEntries];
        }
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
     *   o timecodeRange -- Specifies the timecode range for this block.
     *     Subsequent blocks must contain range numbers greater than or equal to
     *     this one.
     *   o blockPositions -- Specifies a list of byte offsets of the beginnings
     *     of the blocks relative to the beginning of the first Data Packet (for
     *     example, the beginning of the Data Object + 50 bytes).
     *   o indexEntries -- An array that consists of the following keys
     *       o timecode -- This is the 4-byte timecode for these entries.
     *       o offsets -- Specifies the offset. An offset value of 0xffffffff
     *         indicates an invalid offset value.
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
