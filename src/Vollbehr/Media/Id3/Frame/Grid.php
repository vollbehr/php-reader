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
 * The _Group identification registration_ frame enables grouping of
 * otherwise unrelated frames. This can be used when some frames are to be
 * signed. To identify which frames belongs to a set of frames a group
 * identifier must be registered in the tag with this frame.
 * The owner identifier is a URL containing an email address, or a link to a
 * location where an email address can be found, that belongs to the
 * organisation responsible for this grouping. Questions regarding the grouping
 * should be sent to the indicated email address.
 * The group symbol contains a value that associates the frame with this group
 * throughout the whole tag, in the range 0x80-0xf0. All other values are
 * reserved. The group symbol may optionally be followed by some group specific
 * data, e.g. a digital signature. There may be several GRID frames in a tag
 * but only one containing the same symbol and only one containing the same
 * owner identifier. The group symbol must be used somewhere in the tag. See
 * {@see \Vollbehr\Media\Id3\Frame#GROUPING_IDENTITY} for more information.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Grid extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_owner;

    /** @var integer */
    private $_group;

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

        [$this->_owner] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset(strlen((string) $this->_owner) + 1);
        $this->_group = $this->_reader->readUInt8();
        $this->_data  = $this->_reader->read($this->_reader->getSize());
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
     * Returns the group symbol.
     * @return integer
     */
    public function getGroup()
    {
        return $this->_group;
    }

    /**
     * Sets the group symbol.
     * @param integer $group The group symbol.
     */
    public function setGroup($group): void
    {
        $this->_group = $group;
    }

    /**
     * Returns the group dependent data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the group dependent data.
     * @param string $data The data.
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
               ->writeUInt8($this->_group)
               ->write($this->_data);
    }
}
