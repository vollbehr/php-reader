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
 * The purpose of the _Popularimeter_ frame is to specify how good an audio
 * file is. Many interesting applications could be found to this frame such as a
 * playlist that features better audio files more often than others or it could
 * be used to profile a person's taste and find other good files by comparing
 * people's profiles. The frame contains the email address to the user, one
 * rating byte and a four byte play counter, intended to be increased with one
 * for every time the file is played.
 * The rating is 1-255 where 1 is worst and 255 is best. 0 is unknown. If no
 * personal counter is wanted it may be omitted. When the counter reaches all
 * one's, one byte is inserted in front of the counter thus making the counter
 * eight bits bigger in the same away as the play counter
 * {@see \Vollbehr\Media\Id3\Frame\Pcnt PCNT}. There may be more than one POPM frame
 * in each tag, but only one with the same email address.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Popm extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_owner;

    /** @var integer */
    private $_rating = 0;

    /** @var integer */
    private $_counter = 0;

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

        [$this->_owner] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset(strlen((string) $this->_owner) + 1);
        $this->_rating = $this->_reader->readUInt8();

        if ($this->_reader->getSize() - strlen((string) $this->_owner) - 2 > 4) {
            $this->_counter = $this->_reader->readInt64BE();
            // UInt64
        } elseif ($this->_reader->available() > 0) {
            $this->_counter = $this->_reader->readUInt32BE();
        }
    }

    /**
     * Returns the owner identifier string.
     * @return string
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Sets the owner identifier string.
     * @param string $owner The owner identifier string.
     */
    public function setOwner($owner)
    {
        return $this->_owner = $owner;
    }

    /**
     * Returns the user rating.
     * @return integer
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * Sets the user rating.
     * @param integer $rating The user rating.
     */
    public function setRating($rating): void
    {
        $this->_rating = $rating;
    }

    /**
     * Returns the counter.
     * @return integer
     */
    public function getCounter()
    {
        return $this->_counter;
    }

    /**
     * Adds counter by one.
     */
    public function addCounter(): void
    {
        $this->_counter++;
    }

    /**
     * Sets the counter value.
     * @param integer $counter The counter value.
     */
    public function setCounter($counter): void
    {
        $this->_counter = $counter;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8($this->_owner, 1)
               ->writeInt8($this->_rating);
        if ($this->_counter > 0xffffffff) {
            $writer->writeInt64BE($this->_counter);
        } elseif ($this->_counter > 0) {
            $writer->writeUInt32BE($this->_counter);
        }
    }
}
