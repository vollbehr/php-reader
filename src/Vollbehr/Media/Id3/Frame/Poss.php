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
 * The _Position synchronisation frame_ delivers information to the
 * listener of how far into the audio stream he picked up; in effect, it states
 * the time offset from the first frame in the stream. There may only be one
 * POSS frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Poss extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Timing
{
    /** @var integer */
    private $_format = \Vollbehr\Media\Id3\Timing::MPEG_FRAMES;

    /** @var integer */
    private $_position;

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

        $this->_format   = $this->_reader->readUInt8();
        $this->_position = $this->_reader->readUInt32BE();
    }

    /**
     * Returns the timing format.
     * @return integer
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the timing format.
     * @see \Vollbehr\Media\Id3\Timing
     * @param integer $format The timing format.
     */
    public function setFormat($format): void
    {
        $this->_format = $format;
    }

    /**
     * Returns the position where in the audio the listener starts to receive,
     * i.e. the beginning of the next frame.
     * @return integer
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * Sets the position where in the audio the listener starts to receive,
     * i.e. the beginning of the next frame, using given format.
     * @param integer $position The position.
     * @param integer $format The timing format.
     */
    public function setPosition($position, $format = null): void
    {
        $this->_position = $position;
        if ($format !== null) {
            $this->setFormat($format);
        }
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_format)
               ->writeUInt32BE($this->_position);
    }
}
