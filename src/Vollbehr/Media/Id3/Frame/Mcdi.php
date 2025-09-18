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
 * This frame is intended for music that comes from a CD, so that the CD can be
 * identified in databases such as the CDDB. The frame consists of a binary dump
 * of the Table Of Contents, TOC, from the CD, which is a header of 4 bytes and
 * then 8 bytes/track on the CD plus 8 bytes for the lead out, making a
 * maximum of 804 bytes. The offset to the beginning of every track on the CD
 * should be described with a four bytes absolute CD-frame address per track,
 * and not with absolute time. When this frame is used the presence of a valid
 * {@see \Vollbehr\Media\Id3\Frame\Trck TRCK} frame is required, even if the CD's
 * only got one track. It is recommended that this frame is always added to tags
 * originating from CDs.
 * There may only be one MCDI frame in each tag.
 * @author Sven Vollbehr
 */
final class Mcdi extends \Vollbehr\Media\Id3\Frame
{
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

        $this->_data = $this->_reader->read($this->_reader->getSize());
    }

    /**
     * Returns the CD TOC binary dump.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Sets the CD TOC binary dump.
     * @param string $data The CD TOC binary dump string.
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
        $writer->write($this->_data);
    }
}
