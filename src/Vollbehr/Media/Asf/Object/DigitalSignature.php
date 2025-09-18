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
 * The _Digital Signature Object_ lets authors sign the portion of their
 * header that lies between the end of the _File Properties Object_ and the
 * beginning of the _Digital Signature Object_.
 * @author Sven Vollbehr
 */
final class DigitalSignature extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var integer */
    private $_type;

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

        $this->_type = $this->_reader->readUInt32LE();
        $dataLength  = $this->_reader->readUInt32LE();
        $this->_data = $this->_reader->read($dataLength);
    }

    /**
     * Returns the type of digital signature used. This field is set to 2.
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the type of digital signature used. This field must be set to 2.
     * @param integer $type The type of digital signature used.
     */
    public function setType($type): void
    {
        $this->_type = $type;
    }

    /**
     * Returns the digital signature data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the digital signature data.
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
        $this->setSize(24 /* for header */ + 8 + strlen($this->_data));
        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt32LE($this->_type)
               ->writeUInt32LE(strlen($this->_data))
               ->write($this->_data);
    }
}
