<?php

declare(strict_types=1);

namespace Vollbehr\Media\Mpeg\Abs;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * This class represents the Xing VBR header which is often found in the first
 * frame of an MPEG Audio Bit Stream.
 * @author Ryan Butterfield
 * @author Sven Vollbehr
 */
class XingHeader extends BaseObject
{
    /** @var integer */
    private $_frames = false;

    /** @var integer */
    private $_bytes = false;

    private array $_toc = [];

    /** @var integer */
    private $_qualityIndicator = false;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the bitstream.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options Array of options.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $flags = $reader->readUInt32BE();

        if (\Vollbehr\Bit\Twiddling::testAnyBits($flags, 0x1)) {
            $this->_frames = $this->_reader->readUInt32BE();
        }
        if (\Vollbehr\Bit\Twiddling::testAnyBits($flags, 0x2)) {
            $this->_bytes = $this->_reader->readUInt32BE();
        }
        if (\Vollbehr\Bit\Twiddling::testAnyBits($flags, 0x4)) {
            $this->_toc = array_merge(unpack('C*', (string) $this->_reader->read(100)));
        }
        if (\Vollbehr\Bit\Twiddling::testAnyBits($flags, 0x8)) {
            $this->_qualityIndicator = $this->_reader->readUInt32BE();
        }
    }

    /**
     * Returns the number of frames in the file.
     * @return integer
     */
    public function getFrames()
    {
        return $this->_frames;
    }

    /**
     * Returns the number of bytes in the file.
     * @return integer
     */
    public function getBytes()
    {
        return $this->_bytes;
    }

    /**
     * Returns the table of contents array. The returned array has a fixed
     * amount of 100 seek points to the file.
     */
    public function getToc(): array
    {
        return $this->_toc;
    }

    /**
     * Returns the quality indicator. The indicator is from 0 (best quality) to
     * 100 (worst quality).
     * @return integer
     */
    public function getQualityIndicator()
    {
        return $this->_qualityIndicator;
    }

    /**
     * Returns the length of the header in bytes.
     */
    public function getLength(): int
    {
        return 4 +
            ($this->_frames !== false ? 4 : 0) +
            ($this->_bytes !== false ? 4 : 0) +
            ($this->_toc === [] ? 0 : 100) +
            ($this->_qualityIndicator !== false ? 4 : 0);
    }
}
