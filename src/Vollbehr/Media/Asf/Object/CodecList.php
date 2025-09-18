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
 * The _Codec List Object_ provides user-friendly information about the
 * codecs and formats used to encode the content found in the ASF file.
 * @author Sven Vollbehr
 */
final class CodecList extends \Vollbehr\Media\Asf\BaseObject
{
    public const VIDEO_CODEC   = 0x1;
    public const AUDIO_CODEC   = 0x2;
    public const UNKNOWN_CODEC = 0xffff;

    /** @var string */
    private $_reserved;

    /** @var Array */
    private $_entries = [];

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

        $this->_reserved   = $this->_reader->readGuid();
        $codecEntriesCount = $this->_reader->readUInt32LE();
        for ($i = 0; $i < $codecEntriesCount; $i++) {
            $entry              = ['type' => $this->_reader->readUInt16LE()];
            $codecNameLength    = $this->_reader->readUInt16LE() * 2;
            $entry['codecName'] = iconv(
                'utf-16le',
                (string) $this->getOption('encoding'),
                $this->_reader->readString16($codecNameLength)
            );
            $codecDescriptionLength    = $this->_reader->readUInt16LE() * 2;
            $entry['codecDescription'] = iconv(
                'utf-16le',
                (string) $this->getOption('encoding'),
                $this->_reader->readString16($codecDescriptionLength)
            );
            $codecInformationLength    = $this->_reader->readUInt16LE();
            $entry['codecInformation'] = $this->_reader->read($codecInformationLength);
            $this->_entries[]          = $entry;
        }
    }

    /**
     * Returns the array of codec entries. Each record consists of the following
     * keys.
     *   o type -- Specifies the type of the codec used. Use one of the
     *     following values: VIDEO_CODEC, AUDIO_CODEC, or UNKNOWN_CODEC.
     *   o codecName -- Specifies the name of the codec used to create the
     *     content.
     *   o codecDescription -- Specifies the description of the format used to
     *     create the content.
     *   o codecInformation -- Specifies an opaque array of information bytes
     *     about the codec used to create the content. The meaning of these
     *     bytes is determined by the codec.
     * @return Array
     */
    public function getEntries()
    {
        return $this->_entries;
    }

    /**
     * Sets the array of codec entries. Each record must consist of the
     * following keys.
     *   o codecName -- Specifies the name of the codec used to create the
     *     content.
     *   o codecDescription -- Specifies the description of the format used to
     *     create the content.
     *   o codecInformation -- Specifies an opaque array of information bytes
     *     about the codec used to create the content. The meaning of these
     *     bytes is determined by the codec.
     * @param Array $entries The array of codec entries.
     */
    public function setEntries($entries): void
    {
        $this->_entries = $entries;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $codecEntriesCount  = count($this->_entries);
        $codecEntriesWriter = new \Vollbehr\Io\StringWriter();
        for ($i = 0; $i < $codecEntriesCount; $i++) {
            $codecEntriesWriter
                ->writeUInt16LE($this->_entries[$i]['type'])
                ->writeUInt16LE(strlen($codecName = iconv(
                    (string) $this->getOption('encoding'),
                    'utf-16le',
                    (string) $this->_entries[$i]['codecName']
                ) . "\0\0") / 2)
                ->writeString16($codecName)
                ->writeUInt16LE(strlen($codecDescription = iconv(
                    (string) $this->getOption('encoding'),
                    'utf-16le',
                    (string) $this->_entries[$i]['codecDescription']
                ) . "\0\0") / 2)
                ->writeString16($codecDescription)
                ->writeUInt16LE(strlen((string) $this->_entries[$i]['codecInformation']))
                ->write($this->_entries[$i]['codecInformation']);
        }

        $this->setSize(24 /* for header */ + 20 + $codecEntriesWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_reserved)
               ->writeUInt32LE($codecEntriesCount)
               ->write($codecEntriesWriter->toString());
    }
}
