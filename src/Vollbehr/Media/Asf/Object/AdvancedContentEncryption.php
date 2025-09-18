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
 * The _Advanced Content Encryption Object_ lets authors protect content by
 * using Next Generation Windows Media Digital Rights Management for Network
 * Devices.
 * @author Sven Vollbehr
 */
final class AdvancedContentEncryption extends \Vollbehr\Media\Asf\BaseObject
{
    public const WINDOWS_MEDIA_DRM_NETWORK_DEVICES = '7a079bb6-daa4-4e12-a5ca-91d3 8dc11a8d';

    /** @var Array */
    private $_contentEncryptionRecords = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $contentEncryptionRecordsCount = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $contentEncryptionRecordsCount; $i++) {
            $entry = ['systemId' => $this->_reader->readGuid(),
                'systemVersion' => $this->_reader->readUInt32LE(),
                'streamNumbers' => []];
            $encryptedObjectRecordCount = $this->_reader->readUInt16LE();
            for ($j = 0; $j < $encryptedObjectRecordCount; $j++) {
                $this->_reader->skip(4);
                $entry['streamNumbers'][] = $this->_reader->readUInt16LE();
            }
            $dataCount                         = $this->_reader->readUInt32LE();
            $entry['data']                     = $this->_reader->read($dataCount);
            $this->_contentEncryptionRecords[] = $entry;
        }
    }

    /**
     * Returns an array of content encryption records. Each record consists of
     * the following keys.
     *   o systemId -- Specifies the unique identifier for the content
     *     encryption system.
     *   o systemVersion -- Specifies the version of the content encryption
     *     system.
     *   o streamNumbers -- An array of stream numbers a particular Content
     *     Encryption Record is associated with. A value of 0 in this field
     *     indicates that it applies to the whole file; otherwise, the entry
     *     applies only to the indicated stream number.
     *   o data -- The content protection data for this Content Encryption
     *     Record.
     * @return Array
     */
    public function getContentEncryptionRecords()
    {
        return $this->_contentEncryptionRecords;
    }

    /**
     * Sets the array of content encryption records. Each record must consist of
     * the following keys.
     *   o systemId -- Specifies the unique identifier for the content
     *     encryption system.
     *   o systemVersion -- Specifies the version of the content encryption
     *     system.
     *   o streamNumbers -- An array of stream numbers a particular Content
     *     Encryption Record is associated with. A value of 0 in this field
     *     indicates that it applies to the whole file; otherwise, the entry
     *     applies only to the indicated stream number.
     *   o data -- The content protection data for this Content Encryption
     *     Record.
     * @param Array $contentEncryptionRecords The array of content encryption
     *        records.
     */
    public function setContentEncryptionRecords($contentEncryptionRecords): void
    {
        $this->_contentEncryptionRecords = $contentEncryptionRecords;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $contentEncryptionRecordsCount  = count($this->_contentEncryptionRecords);
        $contentEncryptionRecordsWriter = new \Vollbehr\Io\StringWriter();
        for ($i = 0; $i < $contentEncryptionRecordsCount; $i++) {
            $contentEncryptionRecordsWriter
                ->writeGuid($this->_contentEncryptionRecords['systemId'])
                ->writeUInt32LE($this->_contentEncryptionRecords['systemVersion'])
                ->writeUInt16LE($encryptedObjectRecordCount = $this->_contentEncryptionRecords['streamNumbers']);
            for ($j = 0; $j < $encryptedObjectRecordCount; $j++) {
                $contentEncryptionRecordsWriter
                    ->writeUInt16LE(1)
                    ->writeUInt16LE(2)
                    ->writeUInt16LE($this->_contentEncryptionRecords['streamNumbers'][$j]);
            }
            $contentEncryptionRecordsWriter
                ->writeUInt32LE(strlen((string) $this->_contentEncryptionRecords['data']))
                ->write($this->_contentEncryptionRecords['data']);
        }

        $this->setSize(24 /* for header */ + 2 +
             $contentEncryptionRecordsWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt16LE($contentEncryptionRecordsCount)
               ->write($contentEncryptionRecordsWriter->toString());
    }
}
