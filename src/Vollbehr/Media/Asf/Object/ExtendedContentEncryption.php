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
 * The _Extended Content Encryption Object_ lets authors protect content by
 * using the Windows Media Rights Manager 7 Software Development Kit (SDK).
 * @author Sven Vollbehr
 */
final class ExtendedContentEncryption extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
    private $_data;

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

        $dataSize    = $this->_reader->readUInt32LE();
        $this->_data = $this->_reader->read($dataSize);
    }

    /**
     * Returns the array of bytes required by the DRM client to manipulate the
     * protected content.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the array of bytes required by the DRM client to manipulate the
     * protected content.
     * @param string $data The data.
     */
    public function setData($data): void
    {
        $this->_data = $data;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $this->setSize(24 /* for header */ + 4 + strlen($this->_data));
        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt32LE(strlen($this->_data))
               ->write($this->_data);
    }
}
