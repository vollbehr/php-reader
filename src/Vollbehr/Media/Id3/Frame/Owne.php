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
 * The _Ownership frame_ might be used as a reminder of a made transaction
 * or, if signed, as proof. Note that the {@see \Vollbehr\Media\Id3\Frame\User USER}
 * and {@see \Vollbehr\Media\Id3\Frame\Town TOWN} frames are good to use in
 * conjunction with this one.
 * There may only be one OWNE frame in a tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Owne extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Encoding
{
    /** @var integer */
    private $_encoding;

    /** @var string */
    private $_currency = 'EUR';

    private ?string $_price = null;

    /** @var string */
    private $_date;

    /** @var string */
    private $_seller;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setEncoding($this->getOption('encoding', \Vollbehr\Media\Id3\Encoding::UTF8));

        if ($this->_reader === null) {
            return;
        }

        $encoding        = $this->_reader->readUInt8();
        $this->_currency = strtoupper($this->_reader->read(3));
        $offset          = $this->_reader->getOffset();
        [$this->_price]  = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset($offset + strlen((string) $this->_price) + 1);
        $this->_date   = $this->_reader->read(8);
        $this->_seller = $this->_convertString($this->_reader->read($this->_reader->getSize()), $encoding);
    }

    /**
     * Returns the text encoding.
     * All the strings read from a file are automatically converted to the
     * character encoding specified with the <var>encoding</var> option. See
     * {@see \Vollbehr\Media\Id3v2} for details. This method returns that character
     * encoding, or any value set after read, translated into a string form
     * regarless if it was set using a {@see \Vollbehr\Media\Id3\Encoding} constant
     * or a string.
     * @return integer
     */
    public function getEncoding()
    {
        return $this->_translateIntToEncoding($this->_encoding);
    }

    /**
     * Sets the text encoding.
     * All the string written to the frame are done so using given character
     * encoding. No conversions of existing data take place upon the call to
     * this method thus all texts must be given in given character encoding.
     * The character encoding parameter takes either a
     * {@see \Vollbehr\Media\Id3\Encoding} constant or a character set name string
     * in the form accepted by iconv. The default character encoding used to
     * write the frame is 'utf-8'.
     * @see \Vollbehr\Media\Id3\Encoding
     * @param integer $encoding The text encoding.
     */
    public function setEncoding($encoding): void
    {
        $this->_encoding = $this->_translateEncodingToInt($encoding);
    }

    /**
     * Returns the currency code, encoded according to
     * {@see http://www.iso.org/iso/support/faqs/faqs_widely_used_standards/widely_used_standards_other/currency_codes/currency_codes_list-1.htm
     * ISO 4217} alphabetic currency code.
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Sets the currency used in transaction, encoded according to
     * {@see http://www.iso.org/iso/support/faqs/faqs_widely_used_standards/widely_used_standards_other/currency_codes/currency_codes_list-1.htm
     * ISO 4217} alphabetic currency code.
     * @param string $currency The currency code.
     */
    public function setCurrency($currency): void
    {
        $this->_currency = strtoupper($currency);
    }

    /**
     * Returns the price.
     */
    public function getPrice(): float
    {
        return floatval($this->_price);
    }

    /**
     * Sets the price.
     * @param integer $price The price.
     */
    public function setPrice($price): void
    {
        $this->_price = number_format($price, 2, '.', '');
    }

    /**
     * Returns the date describing for how long the price is valid.
     * @internal The ID3v2 standard does not declare the time zone to be used
     *  in the date. Date must thus be expressed as GMT/UTC.
     */
    public function getDate(): \Vollbehr\Date
    {
        $date = new \Vollbehr\Date(0);
        $date->setTimezone('UTC');
        $date->set($this->_date, 'yyyyMMdd');

        return $date;
    }

    /**
     * Sets the date describing for how long the price is valid for.
     * @internal The ID3v2 standard does not declare the time zone to be used
     *  in the date. Date must thus be expressed as GMT/UTC.
     * @param \Vollbehr\Date $date The date.
     */
    public function setDate($date): void
    {
        if ($date === null) {
            $date = \Vollbehr\Date::now();
        }
        $date->setTimezone('UTC');
        $this->_date = $date->toString('yyyyMMdd');
    }

    /**
     * Returns the name of the seller.
     * @return string
     */
    public function getSeller()
    {
        return $this->_seller;
    }

    /**
     * Sets the name of the seller using given encoding.
     * @param string $seller The name of the seller.
     * @param integer $encoding The text encoding.
     */
    public function setSeller($seller, $encoding = null): void
    {
        $this->_seller = $seller;
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding)
               ->write($this->_currency)
               ->writeString8($this->_price, 1)
               ->write($this->_date);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16($this->_seller, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_seller),
            default => $writer->writeString8($this->_seller),
        };
    }
}
