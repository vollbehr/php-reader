<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * To identify with which method a frame has been encrypted the encryption
 * method must be registered in the tag with the _Encryption method
 * registration_ frame.
 * The owner identifier a URL containing an email address, or a link to a
 * location where an email address can be found, that belongs to the
 * organisation responsible for this specific encryption method. Questions
 * regarding the encryption method should be sent to the indicated email
 * address.
 * The method symbol contains a value that is associated with this method
 * throughout the whole tag, in the range 0x80-0xF0. All other values are
 * reserved. The method symbol may optionally be followed by encryption
 * specific data.
 * There may be several ENCR frames in a tag but only one containing the same
 * symbol and only one containing the same owner identifier. The method must be
 * used somewhere in the tag. See {@see \Vollbehr\Media\Id3\Frame#ENCRYPTION} for
 * more information.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Encr extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_owner;

    /** @var integer */
    private $_method;

    /** @var string */
    private $_encryptionData;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($this->_reader === null) {
            return;
        }

        [$this->_owner, ] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset(strlen((string) $this->_owner) + 1);
        $this->_method         = $this->_reader->readInt8();
        $this->_encryptionData = $this->_reader->read($this->_reader->getSize());
    }

    /**
     * Returns the owner identifier string.
     * @return string
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Sets the owner identifier string.
     * @param string $owner The owner identifier string.
     */
    public function setOwner($owner): void
    {
        $this->_owner = $owner;
    }

    /**
     * Returns the method symbol.
     * @return integer
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Sets the method symbol.
     * @param integer $method The method symbol byte.
     */
    public function setMethod($method): void
    {
        $this->_method = $method;
    }

    /**
     * Returns the encryption data.
     * @return string
     */
    public function getEncryptionData()
    {
        return $this->_encryptionData;
    }

    /**
     * Sets the encryption data.
     * @param string $encryptionData The encryption data string.
     */
    public function setEncryptionData($encryptionData): void
    {
        $this->_encryptionData = $encryptionData;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8($this->_owner, 1)
               ->writeInt8($this->_method)
               ->write($this->_encryptionData);
    }
}
