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
 * The _Group Mutual Exclusion Object_ is used to describe mutual exclusion
 * relationships between groups of streams. This object is organized in terms of
 * records, each containing one or more streams, where a stream in record N
 * cannot coexist with a stream in record M for N != M (however, streams in the
 * same record can coexist). This mutual exclusion object would be used
 * typically for the purpose of language mutual exclusion, and a record would
 * consist of all streams for a particular language.
 * @author Sven Vollbehr
 */
final class GroupMutualExclusion extends \Vollbehr\Media\Asf\BaseObject
{
    public const MUTEX_LANGUAGE = 'd6e22a00-35da-11d1-9034-00a0c90349be';
    public const MUTEX_BITRATE  = 'd6e22a01-35da-11d1-9034-00a0c90349be';
    public const MUTEX_UNKNOWN  = 'd6e22a02-35da-11d1-9034-00a0c90349be';

    /** @var string */
    private $_exclusionType;

    /** @var Array */
    private $_records = [];

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
        $recordCount          = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $recordCount; $i++) {
            $streamNumbersCount = $this->_reader->readUInt16LE();
            $streamNumbers      = [];
            for ($j = 0; $j < $streamNumbersCount; $j++) {
                $streamNumbers[] = ['streamNumbers' => $this->_reader->readUInt16LE()];
            }
            $this->_records[] = $streamNumbers;
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
     * @param string $exclusionType The exclusion type.
     */
    public function setExclusionType($exclusionType): void
    {
        $this->_exclusionType = $exclusionType;
    }

    /**
     * Returns an array of records. Each record consists of the following keys.
     *   o streamNumbers -- Specifies the stream numbers for this record. Valid
     *     values are between 1 and 127.
     * @return Array
     */
    public function getRecords()
    {
        return $this->_records;
    }

    /**
     * Sets an array of records. Each record is to consist of the following
     * keys.
     *   o streamNumbers -- Specifies the stream numbers for this record. Valid
     *     values are between 1 and 127.
     * @param Array $records The array of records
     */
    public function setRecords($records): void
    {
        $this->_records = $records;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $recordCount  = count($this->_records);
        $recordWriter = new \Vollbehr\Io\StringWriter();
        for ($i = 0; $i < $recordCount; $i++) {
            $recordWriter
                ->writeUInt16LE($streamNumbersCount = count($this->_records[$i]));
            for ($j = 0; $j < $streamNumbersCount; $j++) {
                $recordWriter->writeUInt16LE($this->_records[$i][$j]['streamNumbers']);
            }
        }

        $this->setSize(24 /* for header */ + $recordWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_exclusionType)
               ->writeUInt16LE($recordCount)
               ->write($recordWriter->toString());
    }
}
