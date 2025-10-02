<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * A base class for all the text frames representing a date or parts of it.
 * @author Sven Vollbehr
 */
abstract class DateFrame extends TextFrame
{
    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     * @param string $_format Rule for formatting output. If null the default
     *  ISO 8601 date format is used.
     */
    public function __construct($reader = null, &$options = [], private $_format = null)
    {
        Frame::__construct($reader, $options);
        $this->setEncoding(Encoding::ISO88591);

        if ($this->_reader === null) {
            return;
        }

        $this->_reader->skip(1);
        $this->setText($this->_reader->readString8($this->_reader->getSize()));
    }

    /**
     * Returns the date.
     * @return \Vollbehr\Date
     */
    public function getDate()
    {
        $date = new \Vollbehr\Date(0);
        $date->setTimezone('UTC');
        $date->set(
            $this->getText(),
            $this->_format ?: \Vollbehr\Date::ISO_8601
        );

        return $date;
    }

    /**
     * Sets the date. If called with null value the current time is entered.
     * @param \Vollbehr\Date $date The date.
     */
    public function setDate($date = null): void
    {
        if ($date === null) {
            $date = \Vollbehr\Date::now();
        }
        $date->setTimezone('UTC');
        $this->setText($date->toString(\Vollbehr\Date::ISO_8601));
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    protected function _writeData($writer)
    {
        $this->setEncoding(Encoding::ISO88591);
        parent::_writeData($writer);
    }
}
