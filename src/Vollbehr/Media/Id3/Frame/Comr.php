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
 * The _Commercial frame_ enables several competing offers in the same tag
 * by bundling all needed information. That makes this frame rather complex but
 * it's an easier solution than if one tries to achieve the same result with
 * several frames.
 * There may be more than one commercial frame in a tag, but no two may be
 * identical.
 * @todo       The use of \Vollbehr\Currency requires design considerations.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Comr extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Encoding
{
    /**
     * The delivery types.
     * @var Array
     */
    public static $types = ['Other', 'Standard CD album with other songs',
         'Compressed audio on CD', 'File over the Internet',
         'Stream over the Internet', 'As note sheets',
         'As note sheets in a book with other sheets', 'Music on other media',
         'Non-musical merchandise'];

    /** @var integer */
    private $_encoding;

    /** @var string */
    private $_currency = 'EUR';

    private ?string $_price = null;

    /** @var string */
    private $_date;

    /** @var string */
    private $_contact;

    /** @var integer */
    private $_delivery = 0;

    /** @var string */
    private $_seller;

    /** @var string */
    private $_description;

    /** @var string */
    private $_mimeType = false;

    /** @var string */
    private $_imageData;

    private int $_imageSize;

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
        $this->_date      = $this->_reader->read(8);
        $offset           = $this->_reader->getOffset();
        [$this->_contact] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset($offset + strlen((string) $this->_contact) + 1);
        $this->_delivery = $this->_reader->readUInt8();
        $offset          = $this->_reader->getOffset();
        switch ($encoding) {
            case self::UTF16:
                // break intentionally omitted
            case self::UTF16BE:
                [$this->_seller, $this->_description] = $this->_explodeString16($this->_reader->read($this->_reader->getSize()), 3);
                $this->_reader->setOffset($offset + strlen((string) $this->_seller) +
                     strlen((string) $this->_description) + 4);
                break;
            case self::UTF8:
                // break intentionally omitted
            default:
                [$this->_seller, $this->_description] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 3);
                $this->_reader->setOffset($offset + strlen((string) $this->_seller) +
                     strlen((string) $this->_description) + 2);
                break;
        }
        $this->_seller      = $this->_convertString($this->_seller, $encoding);
        $this->_description = $this->_convertString($this->_description, $encoding);

        if (!$this->_reader->available()) {
            return;
        }

        [$this->_mimeType, $this->_imageData] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_imageSize                     = strlen((string) $this->_imageData);
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
     * Returns the first price.
     */
    public function getPrice(): float
    {
        $array = explode('/', (string) $this->_price);
        return floatval($array[0]);
    }

    /**
     * Returns the price array.
     */
    public function getPrices(): array
    {
        $array = explode('/', (string) $this->_price);
        foreach ($array as $key => $value) {
            $array[$key] = floatval($value);
        }

        return $array;
    }

    /**
     * Sets the default price. Multiple prices can be given in the form of an
     * array but there may only be one currency of each type.
     * @param double $price The price.
     */
    public function setPrice($price): void
    {
        $this->setPrices($price);
    }

    /**
     * Sets the default price. Multiple prices can be given in the form of an
     * array but there may only be one currency of each type.
     * @param double|Array $prices The prices.
     */
    public function setPrices($prices): void
    {
        if (!is_array($prices)) {
            $prices = [$prices];
        }
        $this->_price = implode('/', $prices);
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
     * Returns the contact URL, with which the user can contact the seller.
     * @return string
     */
    public function getContact()
    {
        return $this->_contact;
    }

    /**
     * Sets the contact URL, with which the user can contact the seller.
     * @param string $contact The contact URL.
     */
    public function setContact($contact): void
    {
        $this->_contact = $contact;
    }

    /**
     * Returns the delivery type with whitch the audio was delivered when
     * bought.
     * @return integer
     */
    public function getDelivery()
    {
        return $this->_delivery;
    }

    /**
     * Sets the delivery type with whitch the audio was delivered when bought.
     * @param integer $delivery The delivery type code.
     */
    public function setDelivery($delivery): void
    {
        $this->_delivery = $delivery;
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
     * Sets the name of the seller using given encoding. The seller text
     * encoding must be that of the description text.
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
     * Returns the short description of the product.
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Sets the content description text using given encoding. The description
     * encoding must be that of the seller text.
     * @param string $description The content description text.
     * @param integer $encoding The text encoding.
     */
    public function setDescription($description, $encoding = null): void
    {
        $this->_description = $description;
        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
    }

    /**
     * Returns the MIME type of the seller's company logo, if attached, or
     * <var>false</var> otherwise. Currently only 'image/png' and 'image/jpeg'
     * are allowed.
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }

    /**
     * Sets the MIME type. Currently only 'image/png' and 'image/jpeg' are
     * allowed. The MIME type is always ISO-8859-1 encoded.
     * @param string $mimeType The MIME type.
     */
    public function setMimeType($mimeType): void
    {
        $this->_mimeType = $mimeType;
    }

    /**
     * Returns the embedded image binary data.
     * @return string
     */
    public function getImageData()
    {
        return $this->_imageData;
    }

    /**
     * Sets the embedded image data. Also updates the image size to correspond
     * the new data.
     * @param string $imageData The image data.
     */
    public function setImageData($imageData): void
    {
        $this->_imageData = $imageData;
        $this->_imageSize = strlen($imageData);
    }

    /**
     * Returns the size of the embedded image data.
     */
    public function getImageSize(): int
    {
        return $this->_imageSize;
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
               ->write($this->_date)
               ->writeString8($this->_contact, 1)
               ->writeUInt8($this->_delivery);
        match ($this->_encoding) {
            self::UTF16LE => $writer->writeString16($this->_seller, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER, 1)
                   ->writeString16(
                       $this->_description,
                       \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER,
                       1
                   ),
            self::UTF16, self::UTF16BE => $writer->writeString16($this->_seller, null, 1)
                   ->writeString16($this->_description, null, 1),
            default => $writer->writeString8($this->_seller, 1)
                   ->writeString8($this->_description, 1),
        };
        if ($this->_mimeType) {
            $writer->writeString8($this->_mimeType, 1)
                   ->write($this->_imageData);
        }
    }
}
