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
 * The _Time_ frame contains the time for the recording in the HHMM format.
 * @author Sven Vollbehr
 * @deprecated ID3v2.3.0
 */
final class Time extends \Vollbehr\Media\Id3\DateFrame
{
    private string $_hours;
    private string $_minutes;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options, 'HHmm');
        $this->_hours   = substr($this->getText(), 0, 2);
        $this->_minutes = substr($this->getText(), 2, 2);
    }

    /**
     * Returns the hour.
     */
    public function getHour(): int
    {
        return intval($this->_hours);
    }

    /**
     * Sets the hour.
     * @param integer $hours The hours.
     */
    public function setHour($hours): void
    {
        $this->setText(
            ($this->_hours = str_pad(strval($hours), 2, '0', STR_PAD_LEFT)) .
             ($this->_minutes !== '' && $this->_minutes !== '0' ? $this->_minutes : '00'),
            \Vollbehr\Media\Id3\Encoding::ISO88591
        );
    }

    /**
     * Returns the minutes.
     */
    public function getMinute(): int
    {
        return intval($this->_minutes);
    }

    /**
     * Sets the minutes.
     * @param integer $minutes The minutes.
     */
    public function setMinute($minutes): void
    {
        $this->setText(
            ($this->_hours !== '' && $this->_hours !== '0' ? $this->_hours : '00') .
             ($this->_minutes = str_pad(strval($minutes), 2, '0', STR_PAD_LEFT)),
            \Vollbehr\Media\Id3\Encoding::ISO88591
        );
    }
}
