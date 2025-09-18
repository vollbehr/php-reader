<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This box is a URN data reference.
 * @author Sven Vollbehr
 */
final class Urn extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var string */
    private $_name;
    /** @var string */
    private $_location;
    /**
     * Indicates that the media data is in the same file as the Movie Box
     * containing this data reference.
     */
    public const SELF_CONTAINED = 1;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        [$this->_name, $this->_location] = preg_split('/\\x00/', (string) $this->_reader->read($this->getOffset() + $this->getSize() -
              $this->_reader->getOffset()));
    }
    /**
     * Returns the name.
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    /**
     * Sets the name.
     * @param string $name The name.
     */
    public function setName($name): void
    {
        $this->_name = $name;
    }
    /**
     * Returns the location.
     * @return string
     */
    public function getLocation()
    {
        return $this->_location;
    }
    /**
     * Sets the location.
     * @param string $location The location.
     */
    public function setLocation($location): void
    {
        $this->_location = $location;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() +
            strlen($this->_name) + 1 + strlen($this->_location);
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeString8($this->_name, 1)
               ->write($this->_location);
    }
}
