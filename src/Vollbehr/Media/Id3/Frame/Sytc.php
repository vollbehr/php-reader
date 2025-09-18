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
 * For a more accurate description of the tempo of a musical piece, the
 * _Synchronised tempo codes_ frame might be used.
 * The tempo data consists of one or more tempo codes. Each tempo code consists
 * of one tempo part and one time part. The tempo is in BPM described with one
 * or two bytes. If the first byte has the value $FF, one more byte follows,
 * which is added to the first giving a range from 2 - 510 BPM, since $00 and
 * $01 is reserved. $00 is used to describe a beat-free time period, which is
 * not the same as a music-free time period. $01 is used to indicate one single
 * beat-stroke followed by a beat-free period.
 * The tempo descriptor is followed by a time stamp. Every time the tempo in the
 * music changes, a tempo descriptor may indicate this for the player. All tempo
 * descriptors must be sorted in chronological order. The first beat-stroke in
 * a time-period is at the same time as the beat description occurs. There may
 * only be one SYTC frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Sytc extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Timing
{
    /**
     * Describes a beat-free time period.
     */
    public const BEAT_FREE = 0x00;

    /**
     * Indicate one single beat-stroke followed by a beat-free period.
     */
    public const SINGLE_BEAT = 0x01;

    /** @var integer */
    private $_format = \Vollbehr\Media\Id3\Timing::MPEG_FRAMES;

    /** @var Array */
    private $_events = [];

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
        $this->_format = $this->_reader->readUInt8();
        while ($this->_reader->available()) {
            $tempo = $this->_reader->readUInt8();
            if ($tempo == 0xff) {
                $tempo += $this->_reader->readUInt8();
            }
            $this->_events[$this->_reader->readUInt32BE()] = $tempo;
        }
        ksort($this->_events);
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
     * Returns the time-bpm tempo events.
     * @return Array
     */
    public function getEvents()
    {
        return $this->_events;
    }

    /**
     * Sets the time-bpm tempo events.
     * @param Array $events The time-bpm tempo events.
     */
    public function setEvents($events): void
    {
        $this->_events = $events;
        ksort($this->_events);
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_format);
        foreach ($this->_events as $timestamp => $tempo) {
            if ($tempo >= 0xff) {
                $writer->writeUInt8(0xff)
                       ->writeUInt8($tempo - 0xff);
            } else {
                $writer->writeUInt8($tempo);
            }
            $writer->writeUInt32BE($timestamp);
        }
    }
}
