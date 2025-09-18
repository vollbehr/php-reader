<?php

declare(strict_types=1);

namespace Vollbehr\Media\Riff\Chunk;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Character Set_ chunk defines the code page and country, language, and dialect codes for the file. These
 * values can be overridden for specific file elements.
 * @author Sven Vollbehr
 */
final class Cset extends \Vollbehr\Media\Riff\Chunk
{
    /** @var integer */
    private $_codePage;

    /** @var integer */
    private $_countryCode;

    /** @var integer */
    private $_language;

    /** @var integer */
    private $_dialect;

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_codePage    = $this->_reader->readUInt16LE();
        $this->_countryCode = $this->_reader->readUInt16LE();
        $this->_language    = $this->_reader->readUInt16LE();
        $this->_dialect     = $this->_reader->readUInt16LE();
    }

    /**
     * Returns the code page used for file elements. If the CSET chunk is not present, or if this field has value zero,
     * assume standard ISO-8859-1 code page (identical to code page 1004 without code points defined in hex columns 0,
     * 1, 8, and 9).
     * @return integer
     */
    final public function getCodePage()
    {
        return $this->_codePage;
    }

    /**
     * Sets the code page used for file elements. Value can be one of the following.
     *   o 000 None (ignore this field)
     *   o 001 USA
     *   o 002 Canada
     *   o 003 Latin America
     *   o 030 Greece
     *   o 031 Netherlands
     *   o 032 Belgium
     *   o 033 France
     *   o 034 Spain
     *   o 039 Italy
     *   o 041 Switzerland
     *   o 043 Austria
     *   o 044 United Kingdom
     *   o 045 Denmark
     *   o 046 Sweden
     *   o 047 Norway
     *   o 049 West Germany
     *   o 052 Mexico
     *   o 055 Brazil
     *   o 061 Australia
     *   o 064 New Zealand
     *   o 081 Japan
     *   o 082 Korea
     *   o 086 People’s Republic of China
     *   o 088 Taiwan
     *   o 090 Turkey
     *   o 351 Portugal
     *   o 352 Luxembourg
     *   o 354 Iceland
     *   o 358 Finland
     */
    final public function setCodePage($codePage): void
    {
        $this->_codePage = $codePage;
    }

    /**
     * Returns the country code used for file elements. See the file format specification for a list of currently
     * defined country codes. If the CSET chunk is not present, or if this field has value zero, assume USA (country
     * code 001).
     * @return integer
     */
    final public function getCountryCode()
    {
        return $this->_countryCode;
    }

    /**
     * Sets the country code used for file elements.
     */
    final public function setCountryCode($countryCode): void
    {
        $this->_countryCode = $countryCode;
    }

    /**
     * Returns the language used for file elements. See the file format specification for a list of language codes.
     * If the CSET chunk is not present, or if these fields have value zero, assume US English (language code 9,
     * dialect code 1).
     * @return integer
     */
    final public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Sets the language used for file elements.
     */
    final public function setLanguage($language): void
    {
        $this->_language = $language;
    }

    /**
     * Returns the dialect used for file elements. See the file format specification for a list of dialect codes.
     * If the CSET chunk is not present, or if these fields have value zero, assume US English (language code 9,
     * dialect code 1).
     * @return integer
     */
    final public function getDialect()
    {
        return $this->_dialect;
    }

    /**
     * Sets the dialect used for file elements.
     */
    final public function setDialect($dialect): void
    {
        $this->_dialect = $dialect;
    }
}
