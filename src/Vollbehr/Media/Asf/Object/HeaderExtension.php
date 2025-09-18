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
 * The _Header Extension Object_ allows additional functionality to be
 * added to an ASF file while maintaining backward compatibility. The Header
 * Extension Object is a container containing zero or more additional extended
 * header objects.
 * @author Sven Vollbehr
 */
final class HeaderExtension extends Container
{
    /** @var string */
    private $_reserved1;

    /** @var integer */
    private $_reserved2;

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
        $this->_reserved2 = $this->_reader->readUInt16LE();
        $this->_reader->skip(4);
        $this->constructObjects(
            [self::EXTENDED_STREAM_PROPERTIES => 'ExtendedStreamProperties',
            self::ADVANCED_MUTUAL_EXCLUSION => 'AdvancedMutualExclusion',
            self::GROUP_MUTUAL_EXCLUSION => 'GroupMutualExclusion',
            self::STREAM_PRIORITIZATION => 'StreamPrioritization',
            self::BANDWIDTH_SHARING => 'BandwidthSharing',
            self::LANGUAGE_LIST => 'LanguageList',
            self::METADATA => 'Metadata',
            self::METADATA_LIBRARY => 'MetadataLibrary',
            self::INDEX_PARAMETERS => 'IndexParameters',
            self::MEDIA_OBJECT_INDEX_PARAMETERS =>
                'MediaObjectIndexParameters',
            self::TIMECODE_INDEX_PARAMETERS => 'TimecodeIndexParameters',
            self::COMPATIBILITY => 'Compatibility',
            self::ADVANCED_CONTENT_ENCRYPTION =>
                'AdvancedContentEncryption',
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

        $this->setSize(24 /* for header */ + 22 + $objectsWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_reserved1)
               ->writeUInt16LE($this->_reserved2)
               ->writeUInt32LE($objectsWriter->getSize())
               ->write($objectsWriter->toString());
    }
}
