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
 * This class represents the Fraunhofer IIS VBR header which is often found in
 * the first frame of an MPEG Audio Bit Stream.
 * @author Ryan Butterfield
 * @author Sven Vollbehr
 */
class VbriHeader extends BaseObject
{
    /** @var integer */
    private $_version;

    /** @var integer */
    private $_delay;

    /** @var integer */
    private $_qualityIndicator;

    /** @var integer */
    private $_bytes;

    /** @var integer */
    private $_frames;

    private array $_toc = [];

    /** @var integer */
    private $_tocFramesPerEntry;

    private readonly float | int $_length;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the bitstream.
     */
    public function __construct()
    {
        $offset                  = $this->_reader->getOffset();
        $this->_version          = $this->_reader->readUInt16BE();
        $this->_delay            = $this->_reader->readUInt16BE();
        $this->_qualityIndicator = $this->_reader->readUInt16BE();
        $this->_bytes            = $this->_reader->readUInt32BE();
        $this->_frames           = $this->_reader->readUInt32BE();

        $tocEntryCount           = $this->_reader->readUInt16BE();
        $tocEntryScale           = $this->_reader->readUInt16BE();
        $tocEntrySize            = $this->_reader->readUInt16BE();
        $this->_tocFramesPerEntry = $this->_reader->readUInt16BE();

        $tocBytes = $this->_reader->read($tocEntryCount * $tocEntrySize);
        if ($tocBytes === false || strlen($tocBytes) !== $tocEntryCount * $tocEntrySize) {
            throw new \RuntimeException('Failed to read VBRI table of contents.');
        }

        $unpackFormat = match ($tocEntrySize) {
            1       => 'C*',
            2       => 'n*',
            4       => 'N*',
            default => throw new \UnexpectedValueException('Unsupported VBRI TOC entry size: ' . $tocEntrySize),
        };

        $this->_toc = array_map(
            static fn (int $value) => $value * $tocEntryScale,
            unpack($unpackFormat, $tocBytes) ?: []
        );

        $this->_length = $this->_reader->getOffset() - $offset;
    }

    /**
     * Returns the header version.
     * @return integer
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Returns the delay.
     * @return integer
     */
    public function getDelay()
    {
        return $this->_delay;
    }

    /**
     * Returns the quality indicator. Return value varies from 0 (best quality)
     * to 100 (worst quality).
     * @return integer
     */
    public function getQualityIndicator()
    {
        return $this->_qualityIndicator;
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
     * Returns the number of frames in the file.
     * @return integer
     */
    public function getFrames()
    {
        return $this->_frames;
    }

    /**
     * Returns the table of contents array.
     */
    public function getToc(): array
    {
        return $this->_toc;
    }

    /**
     * Returns the number of frames per TOC entry.
     * @return integer
     */
    public function getTocFramesPerEntry()
    {
        return $this->_tocFramesPerEntry;
    }

    /**
     * Returns the length of the header in bytes.
     * @return integer
     */
    public function getLength(): int | float
    {
        return $this->_length;
    }
}
