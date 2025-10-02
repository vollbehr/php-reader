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
 * The _Involved people list_ is a frame containing the names of those
 * involved, and how they were involved. There may only be one IPLS frame in
 * each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 * @deprecated ID3v2.3.0
 */
final class Ipls extends \Vollbehr\Media\Id3\Frame implements \Vollbehr\Media\Id3\Encoding
{
    /** @var integer */
    private $_encoding;

    /** @var Array */
    private $_people = [];

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

        $data     = [];
        $encoding = $this->_reader->readUInt8();
        switch ($encoding) {
            case self::UTF16:
                // break intentionally omitted
            case self::UTF16BE:
                $data = $this->_explodeString16($this->_reader->read($this->_reader->getSize()));
                foreach ($data as &$str) {
                    $str = $this->_convertString($str, $encoding);
                }
                break;
            case self::UTF8:
                // break intentionally omitted
            default:
                $data = $this->_convertString(
                    $this->_explodeString8($this->_reader->read($this->_reader->getSize())),
                    $encoding
                );
                break;
        }

        for ($i = 0; $i < count($data) - 1; $i += 2) {
            $this->_people[] = [$data[$i] => @$data[$i + 1]];
        }
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
     * Returns the involved people list as an array. For each person, the array
     * contains an entry, which too is an associate array with involvement as
     * its key and involvee as its value.
     * @return Array
     */
    public function getPeople()
    {
        return $this->_people;
    }
    /**
     * Adds a person with his involvement.
     */
    public function addPerson($involvement, $person): void
    {
        $this->_people[] = [$involvement => $person];
    }
    /**
     * Sets the involved people list array. For each person, the array must
     * contain an associate array with involvement as its key and involvee as
     * its value.
     * @param Array $people The involved people list.
     */
    public function setPeople($people): void
    {
        $this->_people = $people;
    }
    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeUInt8($this->_encoding);
        foreach ($this->_people as $entry) {
            foreach ($entry as $key => $val) {
                match ($this->_encoding) {
                    self::UTF16LE => $writer->writeString16($key, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER, 1)
                           ->writeString16($val, \Vollbehr\Io\Writer::LITTLE_ENDIAN_ORDER, 1),
                    self::UTF16, self::UTF16BE => $writer->writeString16($key, null, 1)
                           ->writeString16($val, null, 1),
                    default => $writer->writeString8($key, 1)
                           ->writeString8($val, 1),
                };
            }
        }
    }
}
