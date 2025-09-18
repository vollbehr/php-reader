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
 * The _ID3v2 Box_ resides under the
 * {@see \Vollbehr\Media\Iso14496\Box\Meta Meta Box} and stores ID3 version 2
 * meta-data. There may be more than one Id3v2 Box present each with a different
 * language code.
 * @author Sven Vollbehr
 */
final class Id32 extends \Vollbehr\Media\Iso14496\FullBox
{
    private string $_language;
    private \Vollbehr\Media\Id3v2 $_tag;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $this->_language = chr(((($tmp = $this->_reader->readUInt16BE()) >> 10) & 0x1f) +
                0x60) .
            chr((($tmp >> 5) & 0x1f) + 0x60) . chr(($tmp & 0x1f) + 0x60);
        $this->_tag = new \Vollbehr\Media\Id3v2($this->_reader, ['readonly' => true]);
    }
    /**
     * Returns the three byte language code to describe the language of this
     * media, according to {@see http://www.loc.gov/standards/iso639-2/
     * ISO 639-2/T}.
     */
    public function getLanguage(): string
    {
        return $this->_language;
    }
    /**
     * Sets the three byte language code as specified in the
     * {@see http://www.loc.gov/standards/iso639-2/ ISO 639-2} standard.
     * @param string $language The language code.
     */
    public function setLanguage(string $language): void
    {
        $this->_language = $language;
    }
    /**
     * Returns the {@see \Vollbehr\Media\Id3v2 Id3v2} tag class instance.
     * @return string
     */
    public function getTag(): \Vollbehr\Media\Id3v2
    {
        return $this->_tag;
    }
    /**
     * Sets the {@see \Vollbehr\Media\Id3v2 Id3v2} tag class instance using given
     * language.
     * @param \Vollbehr\Media\Id3v2 $tag The tag instance.
     * @param string $language The language code.
     */
    public function setTag(\Vollbehr\Media\Id3v2 $tag, $language = null): void
    {
        $this->_tag = $tag;
        if ($language !== null) {
            $this->_language = $language;
        }
    }

    /**
     * Returns the box heap size in bytes.
     * @return integer
     * @todo There has got to be a better way to do this
     */
    public function getHeapSize(): int | float
    {
        $writer = new \Vollbehr\Io\StringWriter();
        $this->_tag->write($writer);
        return parent::getHeapSize() + 2 + $writer->getSize();
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt16BE(((ord($this->_language[0]) - 0x60) << 10) |
              ((ord($this->_language[1]) - 0x60) << 5) |
              ord($this->_language[2]) - 0x60);
        $this->_tag->write($writer);
    }
}
