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
 * The _Original Format Box_ contains the four-character-code of the
 * original un-transformed sample description.
 * @author Sven Vollbehr
 */
final class Frma extends \Vollbehr\Media\Iso14496\Box
{
    /** @var string */
    private $_dataFormat;
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
        $this->_dataFormat = $this->_reader->read(4);
    }

    /**
     * Returns the four-character-code of the original un-transformed sample
     * entry (e.g. _mp4v_ if the stream contains protected MPEG-4 visual
     * material).
     * @return string
     */
    public function getDataFormat()
    {
        return $this->_dataFormat;
    }
    /**
     * Sets the four-character-code of the original un-transformed sample
     * entry (e.g. _mp4v_ if the stream contains protected MPEG-4 visual
     * material).
     * @param string $dataFormat The data format.
     */
    public function setDataFormat($dataFormat): void
    {
        $this->_dataFormat = $dataFormat;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->write(substr($this->_dataFormat, 0, 4));
    }
}
