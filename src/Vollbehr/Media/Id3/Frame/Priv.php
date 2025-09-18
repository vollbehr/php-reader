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
 * The _Private frame_ is used to contain information from a software
 * producer that its program uses and does not fit into the other frames. The
 * frame consists of an owner identifier string and the binary data. The owner
 * identifier is URL containing an email address, or a link to a location where
 * an email address can be found, that belongs to the organisation responsible
 * for the frame. Questions regarding the frame should be sent to the indicated
 * email address. The tag may contain more than one PRIV frame but only with
 * different contents.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Priv extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_owner;

    /** @var string */
    private $_data;

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

        [$this->_owner, $this->_data] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
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
     * Returns the private binary data associated with the frame.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the private binary data associated with the frame.
     * @param string $data The private binary data string.
     */
    public function setData($data): void
    {
        $this->_data = $data;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8($this->_owner, 1)
               ->write($this->_data);
    }
}
