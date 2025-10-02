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
 * The _Content Encryption Object_ lets authors protect content by using
 * Microsoft® Digital Rights Manager version 1.
 * @author Sven Vollbehr
 */
final class ContentEncryption extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
    private $_secretData;

    /** @var string */
    private $_protectionType;

    /** @var string */
    private $_keyId;

    /** @var string */
    private $_licenseUrl;

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

        $secretDataLength      = $this->_reader->readUInt32LE();
        $this->_secretData     = $this->_reader->read($secretDataLength);
        $protectionTypeLength  = $this->_reader->readUInt32LE();
        $this->_protectionType = $this->_reader->readString8($protectionTypeLength);
        $keyIdLength           = $this->_reader->readUInt32LE();
        $this->_keyId          = $this->_reader->readString8($keyIdLength);
        $licenseUrlLength      = $this->_reader->readUInt32LE();
        $this->_licenseUrl     = $this->_reader->readString8($licenseUrlLength);
    }

    /**
     * Returns the secret data.
     * @return string
     */
    public function getSecretData()
    {
        return $this->_secretData;
    }

    /**
     * Sets the secret data.
     * @param string $secretData The secret data.
     */
    public function setSecretData($secretData): void
    {
        $this->_secretData = $secretData;
    }

    /**
     * Returns the type of protection mechanism used. The value of this field
     * is set to 'DRM'.
     * @return string
     */
    public function getProtectionType()
    {
        return $this->_protectionType;
    }

    /**
     * Sets the type of protection mechanism used. The value of this field
     * is to be set to 'DRM'.
     * @param string $protectionType The protection mechanism used.
     */
    public function setProtectionType($protectionType): void
    {
        $this->_protectionType = $protectionType;
    }

    /**
     * Returns the key ID used.
     * @return string
     */
    public function getKeyId()
    {
        return $this->_keyId;
    }

    /**
     * Sets the key ID used.
     * @param string $keyId The key ID used.
     */
    public function setKeyId($keyId): void
    {
        $this->_keyId = $keyId;
    }

    /**
     * Returns the URL from which a license to manipulate the content can be
     * acquired.
     * @return string
     */
    public function getLicenseUrl()
    {
        return $this->_licenseUrl;
    }

    /**
     * Returns the URL from which a license to manipulate the content can be
     * acquired.
     * @param string $licenseUrl The URL from which a license can be acquired.
     */
    public function setLicenseUrl($licenseUrl): void
    {
        $this->_licenseUrl = $licenseUrl;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $buffer = new \Vollbehr\Io\StringWriter();
        $buffer->writeUInt32LE(strlen($this->_secretData))
               ->write($this->_secretData)
               ->writeUInt32LE($len = strlen($this->_protectionType) + 1)
               ->writeString8($this->_protectionType, $len)
               ->writeUInt32LE($len = strlen($this->_keyId) + 1)
               ->writeString8($this->_keyId, $len)
               ->writeUInt32LE($len = strlen($this->_licenseUrl) + 1)
               ->writeString8($this->_licenseUrl, $len);

        $this->setSize(24 /* for header */ + $buffer->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->write($buffer->toString());
    }
}
