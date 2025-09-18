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
 * A base class for all the text frames representing an unsigned integer.
 * @author Sven Vollbehr
 */
abstract class NumberFrame extends TextFrame
{
    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
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
     * Returns the integer value of the text.
     * @return integer
     */
    public function getValue()
    {
        return floatval($this->getText());
    }

    /**
     * Sets the integer value of the text.
     * @param integer $value The integer value of the text.
     */
    public function setValue($value): void
    {
        $this->setText(strval($value), Encoding::ISO88591);
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
