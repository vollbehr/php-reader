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
 * The _Number of a set_ frame is a numeric string that describes which part
 * of a set the audio came from. This frame is used if the source described in
 * the {@see \Vollbehr\Media\Id3\Frame\Talb TALB} frame is divided into several
 * mediums, e.g. a double CD. The value may be extended with a '/' character and
 * a numeric string containing the total number of parts in the set. E.g. '1/2'.
 * @author Sven Vollbehr
 */
final class Tpos extends \Vollbehr\Media\Id3\TextFrame
{
    private ?string $_number = null;
    private ?string $_total  = null;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        \Vollbehr\Media\Id3\Frame::__construct($reader, $options);
        $this->setEncoding(\Vollbehr\Media\Id3\Encoding::ISO88591);

        if ($this->_reader === null) {
            return;
        }

        $this->_reader->skip(1);
        $this->setText($this->_reader->readString8($this->_reader->getSize()));
        @[$this->_number, $this->_total] = explode('/', $this->getText());
    }

    /**
     * Returns the number.
     */
    public function getNumber(): int
    {
        return intval($this->_number);
    }

    /**
     * Sets the number.
     */
    public function setNumber($part): void
    {
        $this->setText(
            $this->_number = strval($part) .
             ($this->_total !== null && $this->_total !== '' && $this->_total !== '0' ? '/' . $this->_total : ''),
            \Vollbehr\Media\Id3\Encoding::ISO88591
        );
    }

    /**
     * Returns the total number.
     */
    public function getTotal(): int
    {
        return intval($this->_total);
    }

    /**
     * Sets the total number.
     * @param integer $total The total number.
     */
    public function setTotal($total): void
    {
        $this->setText(
            ($this->_number !== null && $this->_number !== '' && $this->_number !== '0' ? $this->_number : '?') . '/' .
             ($this->_total = strval($total)),
            \Vollbehr\Media\Id3\Encoding::ISO88591
        );
    }
}
