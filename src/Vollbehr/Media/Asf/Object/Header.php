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
 * The role of the header object is to provide a well-known byte sequence at the
 * beginning of ASF files and to contain all the information that is needed to
 * properly interpret the information within the data object. The header object
 * can optionally contain metadata such as bibliographic information.
 * Of the three top-level ASF objects, the header object is the only one that
 * contains other ASF objects. The header object may include a number of
 * standard objects including, but not limited to:
 *  o File Properties Object -- Contains global file attributes.
 *  o Stream Properties Object -- Defines a digital media stream and its
 *    characteristics.
 *  o Header Extension Object -- Allows additional functionality to be added to
 *    an ASF file while maintaining backward compatibility.
 *  o Content Description Object -- Contains bibliographic information.
 *  o Script Command Object -- Contains commands that can be executed on the
 *    playback timeline.
 *  o Marker Object -- Provides named jump points within a file.
 * Note that objects in the header object may appear in any order. To be valid,
 * the header object must contain a
 * {@see \Vollbehr\Media\Asf\BaseObject\FileProperties File Properties Object}, a
 * {@see \Vollbehr\Media\Asf\BaseObject\HeaderExtension Header Extension Object}, and at
 * least one {@see \Vollbehr\Media\Asf\BaseObject\StreamProperties Stream Properties
 * Object}.
 * @author Sven Vollbehr
 */
final class Header extends Container
{
    /** @var integer */
    private $_reserved1;

    /** @var integer */
    private $_reserved2;

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_reader->skip(4);
        $this->_reserved1 = $this->_reader->readInt8();
        $this->_reserved2 = $this->_reader->readInt8();
        $this->constructObjects(
            [self::FILE_PROPERTIES => 'FileProperties',
            self::STREAM_PROPERTIES => 'StreamProperties',
            self::HEADER_EXTENSION => 'HeaderExtension',
            self::CODEC_LIST => 'CodecList',
            self::SCRIPT_COMMAND => 'ScriptCommand',
            self::MARKER => 'Marker',
            self::BITRATE_MUTUAL_EXCLUSION => 'BitrateMutualExclusion',
            self::ERROR_CORRECTION => 'ErrorCorrection',
            self::CONTENT_DESCRIPTION => 'ContentDescription',
            self::EXTENDED_CONTENT_DESCRIPTION =>
                'ExtendedContentDescription',
            self::CONTENT_BRANDING => 'ContentBranding',
            self::STREAM_BITRATE_PROPERTIES => 'StreamBitrateProperties',
            self::CONTENT_ENCRYPTION => 'ContentEncryption',
            self::EXTENDED_CONTENT_ENCRYPTION =>
                'ExtendedContentEncryption',
            self::DIGITAL_SIGNATURE => 'DigitalSignature',
            self::PADDING => 'Padding']
        );
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $objectsWriter = new \Vollbehr\Io\StringWriter();
        foreach ($this->getObjects() as $objects) {
            foreach ($objects as $object) {
                $object->write($objectsWriter);
            }
        }

        $this->setSize(24 /* for header */ + 6 + $objectsWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt32LE($this->getObjectCount())
               ->writeInt8($this->_reserved1)
               ->writeInt8($this->_reserved2)
               ->write($objectsWriter->toString());
    }
}
