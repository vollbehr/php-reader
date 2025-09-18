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
 * The _Extended Content Description Object_ object implementation. This
 * object contains unlimited number of attribute fields giving more information
 * about the file.
 * @todo       Implement better handling of various types of attributes
 *     according to http://msdn.microsoft.com/en-us/library/aa384495(VS.85).aspx
 * @author Sven Vollbehr
 */
final class ExtendedContentDescription extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var Array */
    private $_contentDescriptors = [];

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

        $contentDescriptorsCount = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $contentDescriptorsCount; $i++) {
            $nameLen = $this->_reader->readUInt16LE();
            $name    = iconv(
                'utf-16le',
                (string) $this->getOption('encoding'),
                $this->_reader->readString16($nameLen)
            );
            $valueDataType = $this->_reader->readUInt16LE();
            $valueLen      = $this->_reader->readUInt16LE();

            switch ($valueDataType) {
                case 0: // string
                    $this->_contentDescriptors[$name] = iconv(
                        'utf-16le',
                        (string) $this->getOption('encoding'),
                        $this->_reader->readString16($valueLen)
                    );
                    break;
                case 1: // byte array
                    $this->_contentDescriptors[$name] = $this->_reader->read($valueLen);
                    break;
                case 2: // bool
                    $this->_contentDescriptors[$name] = $this->_reader->readUInt32LE() == 1;
                    break;
                case 3: // 32-bit integer
                    $this->_contentDescriptors[$name] = $this->_reader->readUInt32LE();
                    break;
                case 4: // 64-bit integer
                    $this->_contentDescriptors[$name] = $this->_reader->readInt64LE();
                    break;
                case 5: // 16-bit integer
                    $this->_contentDescriptors[$name] = $this->_reader->readUInt16LE();
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Returns the value of the specified descriptor or <var>false</var> if
     * there is no such descriptor defined.
     * @param  string $name The name of the descriptor (ie the name of the
     *                      field).
     * @return string|false
     */
    public function getDescriptor($name)
    {
        return $this->_contentDescriptors[$name] ?? false;
    }

    /**
     * Sets the given descriptor a new value.
     * @param  string $name  The name of the descriptor.
     * @param  string $value The value of the field.
     */
    public function setDescriptor($name, $value): void
    {
        $this->_contentDescriptors[$name] = $value;
    }

    /**
     * Returns an associate array of all the descriptors defined having the
     * names of the descriptors as the keys.
     * @return Array
     */
    public function getDescriptors()
    {
        return $this->_contentDescriptors;
    }

    /**
     * Sets the content descriptor associate array having the descriptor names
     * as array keys and their values as associated value. The descriptor names
     * and all string values must be encoded in the default character encoding
     * given as an option to {@see \Vollbehr\Media\Asf} class.
     * @param Array $contentDescriptors The content descriptors
     */
    public function setDescriptors($contentDescriptors): void
    {
        $this->_contentDescriptors = $contentDescriptors;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $contentDescriptorsCount  = count($this->_contentDescriptors);
        $contentDescriptorsWriter = new \Vollbehr\Io\StringWriter();
        foreach ($this->_contentDescriptors as $name => $value) {
            $descriptor = iconv(
                (string) $this->getOption('encoding'),
                'utf-16le',
                $name ? $name . "\0" : ''
            );
            $contentDescriptorsWriter
                ->writeUInt16LE(strlen($descriptor))
                ->writeString16($descriptor);

            if (is_string($value)) {
                /* There is no way to distinguish byte arrays from unicode
                 * strings and hence the need for a list of fields of type
                 * byte array */
                static $byteArray = [
    "W\0M\0/\0M\0C\0D\0I\0\0\0",
    "W\0M\0/\0U\0s\0e\0r\0W\0e\0b\0U\0R\0L\0\0\0",
    "W\0M\0/\0L\0y\0r\0i\0c\0s\0_\0S\0y\0n\0c\0h\0r\0o\0n\0i\0s\0e\0d\0\0\0",
    "W\0M\0/\0P\0i\0c\0t\0u\0r\0e\0\0\0",
                ];
                // TODO: Add to the list if you encounter one
                if (in_array($descriptor, $byteArray)) {
                    $contentDescriptorsWriter
                        ->writeUInt16LE(1)
                        ->writeUInt16LE(strlen($value))
                        ->write($value);
                } else {
                    $value = iconv((string) $this->getOption('encoding'), 'utf-16le', $value) .
                        "\0\0";
                    $contentDescriptorsWriter
                        ->writeUInt16LE(0)
                        ->writeUInt16LE(strlen($value))
                        ->writeString16($value);
                }
            } elseif (is_bool($value)) {
                $contentDescriptorsWriter
                    ->writeUInt16LE(2)
                    ->writeUInt16LE(4)
                    ->writeUInt32LE($value ? 1 : 0);
            } elseif (is_int($value)) {
                $contentDescriptorsWriter
                    ->writeUInt16LE(3)
                    ->writeUInt16LE(4)
                    ->writeUInt32LE($value);
            } elseif (is_float($value)) {
                $contentDescriptorsWriter
                    ->writeUInt16LE(4)
                    ->writeUInt16LE(8)
                    ->writeInt64LE($value);
            } else {
                // Invalid value and there is nothing to be done

                throw new \Vollbehr\Media\Asf\Exception('Invalid data type');
            }
        }

        $this->setSize(24 /* for header */ + 2 + $contentDescriptorsWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt16LE($contentDescriptorsCount)
               ->write($contentDescriptorsWriter->toString());
    }
}
