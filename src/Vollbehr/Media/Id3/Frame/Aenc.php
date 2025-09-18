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
 * The _Audio encryption_ indicates if the actual audio stream is
 * encrypted, and by whom.
 * The identifier is a URL containing an email address, or a link to a location
 * where an email address can be found, that belongs to the organisation
 * responsible for this specific encrypted audio file. Questions regarding the
 * encrypted audio should be sent to the email address specified. There may be
 * more than one AENC frame in a tag, but only one with the same owner
 * identifier.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Aenc extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_owner;

    /** @var integer */
    private $_previewStart;

    /** @var integer */
    private $_previewLength;

    /** @var string */
    private $_encryptionInfo;

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

        [$this->_owner] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset(strlen((string) $this->_owner) + 1);
        $this->_previewStart   = $this->_reader->readUInt16BE();
        $this->_previewLength  = $this->_reader->readUInt16BE();
        $this->_encryptionInfo = $this->_reader->read($this->_reader->getSize());
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
     * Returns the pointer to an unencrypted part of the audio in frames.
     * @return integer
     */
    public function getPreviewStart()
    {
        return $this->_previewStart;
    }

    /**
     * Sets the pointer to an unencrypted part of the audio in frames.
     * @param integer $previewStart The pointer to an unencrypted part.
     */
    public function setPreviewStart($previewStart): void
    {
        $this->_previewStart = $previewStart;
    }

    /**
     * Returns the length of the preview in frames.
     * @return integer
     */
    public function getPreviewLength()
    {
        return $this->_previewLength;
    }

    /**
     * Sets the length of the preview in frames.
     * @param integer $previewLength The length of the preview.
     */
    public function setPreviewLength($previewLength): void
    {
        $this->_previewLength = $previewLength;
    }

    /**
     * Returns the encryption info.
     * @return string
     */
    public function getEncryptionInfo()
    {
        return $this->_encryptionInfo;
    }

    /**
     * Sets the encryption info binary string.
     * @param string $encryptionInfo The data string.
     */
    public function setEncryptionInfo($encryptionInfo): void
    {
        $this->_encryptionInfo = $encryptionInfo;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8($this->_owner, 1)
               ->writeUInt16BE($this->_previewStart)
               ->writeUInt16BE($this->_previewLength)
               ->write($this->_encryptionInfo);
    }
}
