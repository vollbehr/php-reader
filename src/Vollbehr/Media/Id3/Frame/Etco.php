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
 * The _Event timing codes_ allows synchronisation with key events in the
 * audio.
 * The events are an array of timestamp and type pairs. The time stamp is set to
 * zero if directly at the beginning of the sound or after the previous event.
 * All events are sorted in chronological order.
 * The events 0xe0-ef are for user events. You might want to synchronise your
 * music to something, like setting off an explosion on-stage, activating a
 * screensaver etc.
 * There may only be one ETCO frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Etco extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Timing
{
    /**
     * The list of event types.
     * @var Array
     */
    public static $types = ['Padding', 'End of initial silence', 'Intro start', 'Main part start',
         'Outro start', 'Outro end', 'Verse start','Refrain start',
         'Interlude start', 'Theme start', 'Variation start', 'Key change',
         'Time change', 'Momentary unwanted noise', 'Sustained noise',
         'Sustained noise end', 'Intro end', 'Main part end', 'Verse end',
         'Refrain end', 'Theme end', 'Profanity', 'Profanity end',

         0xe0 => 'User event', 'User event', 'User event', 'User event',
         'User event', 'User event', 'User event', 'User event', 'User event',
         'User event', 'User event', 'User event', 'User event', 'User event',

         0xfd => 'Audio end (start of silence)', 'Audio file ends',
         'One more byte of events follows'];

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
            $data                                          = $this->_reader->readUInt8();
            $this->_events[$this->_reader->readUInt32BE()] = $data;
            if ($data == 0xff) {
                break;
            }
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
     * Returns the events as an associated array having the timestamps as keys
     * and the event types as values.
     * @return Array
     */
    public function getEvents()
    {
        return $this->_events;
    }

    /**
     * Sets the events using given format. The value must be an associated array
     * having the timestamps as keys and the event types as values.
     * @param Array $events The events array.
     * @param integer $format The timing format.
     */
    public function setEvents($events, $format = null): void
    {
        $this->_events = $events;
        if ($format !== null) {
            $this->setFormat($format);
        }
        ksort($this->_events);
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_format);
        foreach ($this->_events as $timestamp => $type) {
            $writer->writeUInt8($type)
                   ->writeUInt32BE($timestamp);
        }
    }
}
