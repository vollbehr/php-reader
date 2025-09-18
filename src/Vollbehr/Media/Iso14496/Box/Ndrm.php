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
 * The _Nero Digital Rights Management Box_.
 * @author Sven Vollbehr
 */
final class Ndrm extends \Vollbehr\Media\Iso14496\FullBox
{
    private $_data;

    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_data = $reader->read($this->getSize() - 12);
    }

    /**
     * Returns the raw binary data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
    /**
     * Sets the raw binary data.
     * @param string $data The data.
     */
    public function setData($data): void
    {
        $this->_data = $data;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() + $this->getSize() - 12;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->write($this->_data);
    }
}
