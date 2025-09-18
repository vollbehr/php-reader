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
 * The _Bitrate Mutual Exclusion Object_ identifies video streams that have
 * a mutual exclusion relationship to each other (in other words, only one of
 * the streams within such a relationship can be streamed at any given time and
 * the rest are ignored). One instance of this object must be present for each
 * set of objects that contains a mutual exclusion relationship. All video
 * streams in this relationship must have the same frame size. The exclusion
 * type is used so that implementations can allow user selection of common
 * choices, such as bit rate.
 * @author Sven Vollbehr
 */
final class BitrateMutualExclusion extends \Vollbehr\Media\Asf\BaseObject
{
    public const MUTEX_LANGUAGE = 'd6e22a00-35da-11d1-9034-00a0c90349be';
    public const MUTEX_BITRATE  = 'd6e22a01-35da-11d1-9034-00a0c90349be';
    public const MUTEX_UNKNOWN  = 'd6e22a02-35da-11d1-9034-00a0c90349be';

    /** @var string */
    private $_exclusionType;

    /** @var Array */
    private $_streamNumbers = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $this->_exclusionType = $this->_reader->readGuid();
        $streamNumbersCount   = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $streamNumbersCount; $i++) {
            $this->_streamNumbers[] = $this->_reader->readUInt16LE();
        }
    }

    /**
     * Returns the nature of the mutual exclusion relationship.
     * @return string
     */
    public function getExclusionType()
    {
        return $this->_exclusionType;
    }

    /**
     * Sets the nature of the mutual exclusion relationship.
     * @param string $exclusionType The nature of the mutual exclusion
     *        relationship.
     */
    public function setExclusionType($exclusionType): void
    {
        $this->_exclusionType = $exclusionType;
    }

    /**
     * Returns an array of stream numbers.
     * @return Array
     */
    public function getStreamNumbers()
    {
        return $this->_streamNumbers;
    }

    /**
     * Sets the array of stream numbers.
     * @param Array $streamNumbers The array of stream numbers.
     */
    public function setStreamNumbers($streamNumbers): void
    {
        $this->_streamNumbers = $streamNumbers;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $streamNumbersCount = count($this->_streamNumbers);
        $this->setSize(24 /* for header */ + 18 + $streamNumbersCount * 2);

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_exclusionType)
               ->writeUInt16LE($streamNumbersCount);
        for ($i = 0; $i < $streamNumbersCount; $i++) {
            $writer->writeUInt16LE($this->_streamNumbers[$i]);
        }
    }
}
