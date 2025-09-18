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
 * This frame enables a group of frames, grouped with the
 * _Group identification registration_, to be signed. Although signatures
 * can reside inside the registration frame, it might be desired to store the
 * signature elsewhere, e.g. in watermarks. There may be more than one signature
 * frame in a tag, but no two may be identical.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 * @since      ID3v2.4.0
 */
final class Sign extends \Vollbehr\Media\Id3\Frame
{
    /** @var integer */
    private $_group;

    /** @var string */
    private $_signature;

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

        $this->_group     = $this->_reader->readUInt8();
        $this->_signature = $this->_reader->read($this->_reader->getSize());
    }

    /**
     * Returns the group symbol byte.
     * @return integer
     */
    public function getGroup()
    {
        return $this->_group;
    }
    /**
     * Sets the group symbol byte.
     * @param integer $group The group symbol byte.
     */
    public function setGroup($group): void
    {
        $this->_group = $group;
    }
    /**
     * Returns the signature binary data.
     * @return string
     */
    public function getSignature()
    {
        return $this->_signature;
    }
    /**
     * Sets the signature binary data.
     * @param string $signature The signature binary data string.
     */
    public function setSignature($signature): void
    {
        $this->_signature = $signature;
    }
    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_group)
               ->write($this->_signature);
    }
}
