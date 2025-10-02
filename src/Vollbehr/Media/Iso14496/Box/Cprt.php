<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Copyright Box_ contains a copyright declaration which applies to
 * the entire presentation, when contained within the
 * {@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}, or, when contained in a
 * track, to that entire track. There may be multiple copyright boxes using
 * different language codes.
 * @author Sven Vollbehr
 */
final class Cprt extends \Vollbehr\Media\Iso14496\FullBox
{
    private string $_language;
    /** @var string */
    private $_notice;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     * @todo Distinguish UTF-16?
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_language = chr(((($tmp = $this->_reader->readUInt16BE()) >> 10) & 0x1f) + 0x60) .
            chr((($tmp >> 5) & 0x1f) + 0x60) . chr(($tmp & 0x1f) + 0x60);
        $this->_notice = $this->_reader->readString8($this->getOffset() + $this->getSize() -
             $this->_reader->getOffset());
    }
    /**
     * Returns the three byte language code to describe the language of the
     * notice, according to {@see http://www.loc.gov/standards/iso639-2/
     * ISO 639-2/T}.
     */
    public function getLanguage(): string
    {
        return $this->_language;
    }
    /**
     * Sets the three byte language code to describe the language of this
     * media, according to {@see http://www.loc.gov/standards/iso639-2/
     * ISO 639-2/T}.
     * @param string $language The language code.
     */
    public function setLanguage(string $language): void
    {
        $this->_language = $language;
    }
    /**
     * Returns the copyright notice.
     * @return string
     */
    public function getNotice()
    {
        return $this->_notice;
    }
    /**
     * Returns the copyright notice.
     * @param string $notice The copyright notice.
     */
    public function setNotice($notice): void
    {
        $this->_notice = $notice;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 3 + strlen($this->_notice);
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt16BE((ord($this->_language[0]) - 0x60) << 10 |
                (ord($this->_language[1]) - 0x60) << 5 |
                 (ord($this->_language[2]) - 0x60))
               ->writeString8($this->_notice, 1);
    }
}
