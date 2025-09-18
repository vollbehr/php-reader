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
 * The _Marker Object_ class.
 * @author Sven Vollbehr
 */
final class Marker extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
    private $_reserved1;

    /** @var integer */
    private $_reserved2;

    private string | false $_name;

    /** @var Array */
    private $_markers = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_reserved1 = $this->_reader->readGuid();
        $markersCount     = $this->_reader->readUInt32LE();
        $this->_reserved2 = $this->_reader->readUInt16LE();
        $nameLength       = $this->_reader->readUInt16LE();
        $this->_name      = iconv(
            'utf-16le',
            (string) $this->getOption('encoding'),
            $this->_reader->readString16($nameLength)
        );
        for ($i = 0; $i < $markersCount; $i++) {
            $marker = ['offset' => $this->_reader->readInt64LE(),
                 'presentationTime' => $this->_reader->readInt64LE()];
            $this->_reader->skip(2);
            $marker['sendTime']    = $this->_reader->readUInt32LE();
            $marker['flags']       = $this->_reader->readUInt32LE();
            $descriptionLength     = $this->_reader->readUInt32LE();
            $marker['description'] = iconv(
                'utf-16le',
                (string) $this->getOption('encoding'),
                $this->_reader->readString16($descriptionLength)
            );
            $this->_markers[] = $marker;
        }
    }

    /**
     * Returns the name of the Marker Object.
     * @return Array
     */
    public function getName(): string | bool
    {
        return $this->_name;
    }

    /**
     * Returns the name of the Marker Object.
     * @param string $name The name.
     */
    public function setName(string | bool $name): void
    {
        $this->_name = $name;
    }

    /**
     * Returns an array of markers. Each entry consists of the following keys.
     *   o offset -- Specifies a byte offset into the _Data Object_ to the
     *     actual position of the marker in the _Data Object_. ASF parsers
     *     must seek to this position to properly display data at the specified
     *     marker _Presentation Time_.
     *   o presentationTime -- Specifies the presentation time of the marker, in
     *     100-nanosecond units.
     *   o sendTime -- Specifies the send time of the marker entry, in
     *     milliseconds.
     *   o flags -- Flags are reserved and should be set to 0.
     *   o description -- Specifies a description of the marker entry.
     * @return Array
     */
    public function getMarkers()
    {
        return $this->_markers;
    }

    /**
     * Sets the array of markers. Each entry is to consist of the following
     * keys.
     *   o offset -- Specifies a byte offset into the _Data Object_ to the
     *     actual position of the marker in the _Data Object_. ASF parsers
     *     must seek to this position to properly display data at the specified
     *     marker _Presentation Time_.
     *   o presentationTime -- Specifies the presentation time of the marker, in
     *     100-nanosecond units.
     *   o sendTime -- Specifies the send time of the marker entry, in
     *     milliseconds.
     *   o flags -- Flags are reserved and should be set to 0.
     *   o description -- Specifies a description of the marker entry.
     * @param Array $markers The array of markers.
     */
    public function setMarkers($markers): void
    {
        $this->_markers = $markers;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $name          = iconv((string) $this->getOption('encoding'), 'utf-16le', $this->_name) . "\0\0";
        $markersCount  = count($this->_markers);
        $markersWriter = new \Vollbehr\Io\StringWriter();
        for ($i = 0; $i < $markersCount; $i++) {
            $markersWriter
                ->writeInt64LE($this->_markers[$i]['offset'])
                ->writeInt64LE($this->_markers[$i]['presentationTime'])
                ->writeUInt16LE(12 + ($descriptionLength = strlen($description = iconv(
                    'utf-16le',
                    (string) $this->getOption('encoding'),
                    (string) $this->_markers[$i]['description']
                ) . "\0\0")))
                ->writeUInt32LE($this->_markers[$i]['sendTime'])
                ->writeUInt32LE($this->_markers[$i]['flags'])
                ->writeUInt32LE($descriptionLength)
                ->writeString16($description);
        }

        $this->setSize(24 /* for header */ + 24 + strlen($name) +
             $markersWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_reserved1)
               ->writeUInt32LE($markersCount)
               ->writeUInt16LE($this->_reserved2)
               ->writeUInt16LE(strlen($name))
               ->writeString16($name)
               ->write($markersWriter->toString());
    }
}
