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
 * The _Metadata Object_ permits authors to store stream-based metadata in
 * a file. This object supports the same types of metadata information as the
 * _Extended Content Description Object_ except that it also allows a
 * stream number to be specified.
 * @todo       Implement better handling of various types of attributes
 *     according to http://msdn.microsoft.com/en-us/library/aa384495(VS.85).aspx
 * @author Sven Vollbehr
 */
final class Metadata extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var Array */
    private $_descriptionRecords = [];

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

        $descriptionRecordsCount = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $descriptionRecordsCount; $i++) {
            $this->_reader->skip(2);
            $descriptionRecord         = ['streamNumber' => $this->_reader->readUInt16LE()];
            $nameLength                = $this->_reader->readUInt16LE();
            $dataType                  = $this->_reader->readUInt16LE();
            $dataLength                = $this->_reader->readUInt32LE();
            $descriptionRecord['name'] = iconv(
                'utf-16le',
                (string) $this->getOption('encoding'),
                $this->_reader->readString16($nameLength)
            );
            switch ($dataType) {
                case 0: // Unicode string
                    $descriptionRecord['data'] = iconv(
                        'utf-16le',
                        (string) $this->getOption('encoding'),
                        $this->_reader->readString16($dataLength)
                    );
                    break;
                case 1: // BYTE array
                    $descriptionRecord['data'] = $this->_reader->read($dataLength);
                    break;
                case 2: // BOOL
                    $descriptionRecord['data'] = $this->_reader->readUInt16LE() == 1;
                    break;
                case 3: // DWORD
                    $descriptionRecord['data'] = $this->_reader->readUInt32LE();
                    break;
                case 4: // QWORD
                    $descriptionRecord['data'] = $this->_reader->readInt64LE();
                    break;
                case 5: // WORD
                    $descriptionRecord['data'] = $this->_reader->readUInt16LE();
                    break;
                default:
                    break;
            }
            $this->_descriptionRecords[] = $descriptionRecord;
        }
    }

    /**
     * Returns the array of description records. Each record consists of the
     * following keys.
     *   o streamNumber -- Specifies the stream number. Valid values are between
     *     1 and 127.
     *   o name -- Specifies the name that uniquely identifies the attribute
     *     being described. Names are case-sensitive.
     *   o data -- Specifies the actual metadata being stored.
     * @return Array
     */
    public function getDescriptionRecords()
    {
        return $this->_descriptionRecords;
    }

    /**
     * Sets the array of description records. Each record must consist of the
     * following keys.
     *   o streamNumber -- Specifies the stream number. Valid values are between
     *     1 and 127.
     *   o name -- Specifies the name that uniquely identifies the attribute
     *     being described. Names are case-sensitive.
     *   o data -- Specifies the actual metadata being stored.
     * @param Array $descriptionRecords The array of description records.
     */
    public function setDescriptionRecords($descriptionRecords): void
    {
        $this->_descriptionRecords = $descriptionRecords;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $descriptionRecordsCount  = count($this->_descriptionRecords);
        $descriptionRecordsWriter = new \Vollbehr\Io\StringWriter();
        for ($i = 0; $i < $descriptionRecordsCount; $i++) {
            $descriptionRecordsWriter
                ->writeUInt16LE(0)
                ->writeUInt16LE($this->_descriptionRecords[$i]['streamNumber'])
                ->writeUInt16LE(strlen($name = iconv(
                    (string) $this->getOption('encoding'),
                    'utf-16le',
                    (string) $this->_descriptionRecords[$i]['name']
                ) . "\0\0"));
            if (is_string($this->_descriptionRecords[$i]['data'])) {
                /* There is no way to distinguish byte arrays from unicode
                 * strings and hence the need for a list of fields of type byte
                 * array */
                static $byteArray = [
                    '',
                ];
                // TODO: Add to the list if you encounter one
                if (in_array($name, $byteArray)) {
                    $descriptionRecordsWriter
                        ->writeUInt16LE(1)
                        ->writeUInt32LE(strlen($this->_descriptionRecords[$i]['data']))
                        ->write($name)
                        ->write($this->_descriptionRecords[$i]['data']);
                } else {
                    $value = iconv(
                        (string) $this->getOption('encoding'),
                        'utf-16le',
                        $this->_descriptionRecords[$i]['data']
                    );
                    $value = ($value ? $value . "\0\0" : '');
                    $descriptionRecordsWriter
                        ->writeUInt16LE(0)
                        ->writeUInt32LE(strlen($value))
                        ->write($name)
                        ->writeString16($value);
                }
            } elseif (is_bool($this->_descriptionRecords[$i]['data'])) {
                $descriptionRecordsWriter
                    ->writeUInt16LE(2)
                    ->writeUInt32LE(2)
                    ->write($name)
                    ->writeUInt16LE($this->_descriptionRecords[$i]['data'] ? 1 : 0);
            } elseif (is_int($this->_descriptionRecords[$i]['data'])) {
                $descriptionRecordsWriter
                    ->writeUInt16LE(3)
                    ->writeUInt32LE(4)
                    ->write($name)
                    ->writeUInt32LE($this->_descriptionRecords[$i]['data']);
            } elseif (is_float($this->_descriptionRecords[$i]['data'])) {
                $descriptionRecordsWriter
                    ->writeUInt16LE(4)
                    ->writeUInt32LE(8)
                    ->write($name)
                    ->writeInt64LE($this->_descriptionRecords[$i]['data']);
            } else {
                // Invalid value and there is nothing to be done

                throw new \Vollbehr\Media\Asf\Exception('Invalid data type');
            }
        }

        $this->setSize(24 /* for header */ + 2 + $descriptionRecordsWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt16LE($descriptionRecordsCount)
               ->write($descriptionRecordsWriter->toString());
    }
}
