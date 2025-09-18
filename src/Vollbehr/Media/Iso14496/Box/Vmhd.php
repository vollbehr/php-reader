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
 * The _Video Media Header Box_ contains general presentation information,
 * independent of the coding, for video media.
 * @author Sven Vollbehr
 */
final class Vmhd extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_graphicsMode = 0;
    private array $_opcolor;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            $this->setFlags(1);

            return;
        }
        $this->_graphicsMode = $this->_reader->readUInt16BE();
        $this->_opcolor      = [$this->_reader->readUInt16BE(),
                 $this->_reader->readUInt16BE(),
                 $this->_reader->readUInt16BE()];
    }

    /**
     * Returns the composition mode for this video track, from the following
     * enumerated set, which may be extended by derived specifications:
     * - copy = 0 copy over the existing image
     * @return integer
     */
    public function getGraphicsMode()
    {
        return $this->_graphicsMode;
    }
    /**
     * Sets the composition mode for this video track.
     * @param integer $graphicsMode The composition mode.
     */
    public function setGraphicsMode($graphicsMode): void
    {
        $this->_graphicsMode = $graphicsMode;
    }
    /**
     * Returns an array of 3 colour values (red, green, blue) available for use
     * by graphics modes.
     */
    public function getOpcolor(): array
    {
        return $this->_opcolor;
    }
    /**
     * Sets the array of 3 colour values (red, green, blue) available for use
     * by graphics modes.
     * @param Array $opcolor An array of 3 colour values
     */
    public function setOpcolor(array $opcolor): void
    {
        $this->_opcolor = $opcolor;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 8;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt16BE($this->_graphicsMode)
               ->writeUInt16BE($this->_opcolor[0])
               ->writeUInt16BE($this->_opcolor[1])
               ->writeUInt16BE($this->_opcolor[2]);
    }
}
