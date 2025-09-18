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
 * The _Seek_ frame indicates where other tags in a file/stream can be
 * found. The minimum offset to next tag is calculated from the end of this tag
 * to the beginning of the next. There may only be one seek frame in a tag.
 * @author Sven Vollbehr
 * @since      ID3v2.4.0
 */
final class Seek extends \Vollbehr\Media\Id3\Frame
{
    /** @var integer */
    private $_minOffset;

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

        $this->_minOffset = $this->_reader->readInt32BE();
    }

    /**
     * Returns the minimum offset to next tag in bytes.
     * @return integer
     */
    public function getMinimumOffset()
    {
        return $this->_minOffset;
    }
    /**
     * Sets the minimum offset to next tag in bytes.
     * @param integer $minOffset The minimum offset.
     */
    public function setMinimumOffset($minOffset): void
    {
        $this->_minOffset = $minOffset;
    }
    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeInt32BE($this->_minOffset);
    }
}
