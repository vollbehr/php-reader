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
 * The _TSRC_ frame should contain the International Standard Recording
 * Code or ISRC (12 characters).
 * @author Sven Vollbehr
 */
final class Tsrc extends \Vollbehr\Media\Id3\TextFrame
{
    private string $_country;

    private string $_registrant;

    private string $_year;

    private string $_uniqueNumber;

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
        $this->_country      = substr($this->getText(), 0, 2);
        $this->_registrant   = substr($this->getText(), 2, 3);
        $this->_year         = substr($this->getText(), 5, 2);
        $this->_uniqueNumber = substr($this->getText(), 7, 5);
    }

    /**
     * Returns the appropriate for the registrant the two-character ISO 3166-1
     * alpha-2 country code.
     */
    public function getCountry(): string
    {
        return $this->_country;
    }

    /**
     * Sets the country.
     * @param string $country The two-character ISO 3166-1 alpha-2 country code.
     */
    public function setCountry(string $country): void
    {
        $this->_country = $country;
    }

    /**
     * Returns the three character alphanumeric registrant code, uniquely
     * identifying the organisation which registered the ISRC code.
     */
    public function getRegistrant(): string
    {
        return $this->_registrant;
    }

    /**
     * Sets the registrant.
     * @param string $registrant The three character alphanumeric registrant
     *  code.
     */
    public function setRegistrant(string $registrant): void
    {
        $this->_registrant = $registrant;
    }

    /**
     * Returns the year of registration.
     */
    public function getYear(): int
    {
        $year = intval($this->_year);
        if ($year > 50) {
            return 1900 + $year;
        } else {
            return 2000 + $year;
        }
    }

    /**
     * Sets the year.
     * @param integer $year The year of registration.
     */
    public function setYear($year): void
    {
        $this->_year = substr(strval($year), 2, 2);
    }

    /**
     * Returns the unique number identifying the particular sound recording.
     */
    public function getUniqueNumber(): int
    {
        return intval($this->_uniqueNumber);
    }

    /**
     * Sets the unique number.
     * @param integer $uniqueNumber The unique number identifying the
     *  particular sound recording.
     */
    public function setUniqueNumber($uniqueNumber): void
    {
        $this->_uniqueNumber = str_pad(strval($uniqueNumber), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Returns the whole ISRC code in the form
     * "country-registrant-year-unique number".
     */
    public function getIsrc(): string
    {
        return $this->_country . '-' . $this->_registrant . '-' .
            $this->_year . '-' . $this->_uniqueNumber;
    }

    /**
     * Sets the ISRC code in the form "country-registrant-year-unique number".
     * @param string $isrc The ISRC code.
     */
    public function setIsrc($isrc): void
    {
        [$this->_country, $this->_registrant, $this->_year, $this->_uniqueNumber] = preg_split('/-/', $isrc);
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $this->setText($this->_country . $this->_registrant . $this->_year .
             $this->_uniqueNumber, \Vollbehr\Media\Id3\Encoding::ISO88591);
        parent::_writeData($writer);
    }
}
