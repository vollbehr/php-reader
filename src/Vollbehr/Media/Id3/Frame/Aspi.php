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
 * Audio files with variable bit rates are intrinsically difficult to deal with
 * in the case of seeking within the file. The _Audio seek point index_ or
 * ASPI frame makes seeking easier by providing a list a seek points within the
 * audio file. The seek points are a fractional offset within the audio data,
 * providing a starting point from which to find an appropriate point to start
 * decoding. The presence of an ASPI frame requires the existence of a
 * {@see \Vollbehr\Media\Id3\Frame\Tlen TLEN} frame, indicating the duration of the
 * file in milliseconds. There may only be one audio seek point index frame in
 * a tag.
 * @todo       Data parsing and write support
 * @author Sven Vollbehr
 * @since      ID3v2.4.0
 */
final class Aspi extends \Vollbehr\Media\Id3\Frame
{
    /** @var integer */
    private $_dataStart;

    /** @var integer */
    private $_dataLength;

    private array $_fractions = [];

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

            throw new \Vollbehr\Media\Id3\Exception('Write not supported yet');
        }

        $this->_dataStart  = $this->_reader->readInt32BE();
        $this->_dataLength = $this->_reader->readInt32BE();
        $this->_reader->readInt8();
        /*for ($i = 0, $offset = 11; $i < $this->_size; $i++) {
            if ($bitsPerPoint == 16) {
                $this->_fractions[$i] = substr($this->_data, $offset, 2);
                $offset += 2;
            } else {
                $this->_fractions[$i] = substr($this->_data, $offset, 1);
                $offset ++;
            }
        }*/
    }

    /**
     * Returns the byte offset from the beginning of the file.
     * @return integer
     */
    public function getDataStart()
    {
        return $this->_dataStart;
    }
    /**
     * Sets the byte offset from the beginning of the file.
     * @param integer $dataStart The offset.
     */
    public function setDataStart($dataStart): void
    {
        $this->_dataStart = $dataStart;
    }
    /**
     * Returns the byte length of the audio data being indexed.
     * @return integer
     */
    public function getDataLength()
    {
        return $this->_dataLength;
    }
    /**
     * Sets the byte length of the audio data being indexed.
     * @param integer $dataLength The length.
     */
    public function setDataLength($dataLength): void
    {
        $this->_dataLength = $dataLength;
    }
    /**
     * Returns the number of index points in the frame.
     */
    public function getSize(): int
    {
        return count($this->_fractions);
    }
    /**
     * Returns the numerator of the fraction representing a relative position in
     * the data or <var>false</var> if index not defined. The denominator is 2
     * to the power of b.
     * @param integer $index The fraction numerator.
     * @return integer
     */
    public function getFractionAt($index)
    {
        return $this->_fractions[$index] ?? false;
    }
}
