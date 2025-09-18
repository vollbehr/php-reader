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
 * The _Error Correction Object_ defines the error correction method. This
 * enables different error correction schemes to be used during content
 * creation. The _Error Correction Object_ contains provisions for opaque
 * information needed by the error correction engine for recovery. For example,
 * if the error correction scheme were a simple N+1 parity scheme, then the
 * value of N would have to be available in this object.
 * Note that this does not refer to the same thing as the _Error Correction
 * Type_ field in the _{@see \Vollbehr\Media\Asf\BaseObject\StreamProperties Stream
 * Properties Object}_.
 * @author Sven Vollbehr
 */
final class ErrorCorrection extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
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

        $this->_type = $this->_reader->readGuid();
        $dataLength  = $this->_reader->readUInt32LE();
        $this->_data = $this->_reader->read($dataLength);
    }

    /**
     * Returns the type of error correction.
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the type of error correction.
     * @param string $type The type of error correction.
     */
    public function setType($type): void
    {
        $this->_type = $type;
    }

    /**
     * Returns the data specific to the error correction scheme. The structure
     * for the _Error Correction Data_ field is determined by the value
     * stored in the _Error Correction Type_ field.
     * @return Array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the data specific to the error correction scheme. The structure for
     * the _Error Correction Data_ field is determined by the value stored
     * in the _Error Correction Type_ field.
     * @param Array $data The error correction specific data.
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
        $this->setSize(24 /* for header */ + 20 + strlen($this->_data));
        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_type)
               ->writeUInt32LE(strlen($this->_data))
               ->write($this->_data);
    }
}
