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
 * The _Scheme Type Box_ identifies the protection scheme.
 * @author Sven Vollbehr
 */
final class Schm extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var string */
    private $_schemeType;
    /** @var integer */
    private $_schemeVersion;
    /** @var string */
    private $_schemeUri;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_schemeType    = $this->_reader->read(4);
        $this->_schemeVersion = $this->_reader->readUInt32BE();
        if ($this->hasFlag(1)) {
            $this->_schemeUri = preg_split('/\\x00/', (string) $this->_reader->read($this->getOffset() + $this->getSize() -
                  $this->_reader->getOffset()));
        }
    }
    /**
     * Returns the code defining the protection scheme.
     * @return string
     */
    public function getSchemeType()
    {
        return $this->_schemeType;
    }
    /**
     * Sets the code defining the protection scheme.
     * @param string $schemeType The scheme type.
     */
    public function setSchemeType($schemeType): void
    {
        $this->_schemeType = $schemeType;
    }
    /**
     * Returns the version of the scheme used to create the content.
     * @return integer
     */
    public function getSchemeVersion()
    {
        return $this->_schemeVersion;
    }
    /**
     * Sets the version of the scheme used to create the content.
     * @param integer $schemeVersion The scheme version.
     */
    public function setSchemeVersion($schemeVersion): void
    {
        $this->_schemeVersion = $schemeVersion;
    }
    /**
     * Returns the optional scheme address to allow for the option of directing
     * the user to a web-page if they do not have the scheme installed on their
     * system. It is an absolute URI.
     * @return string
     */
    public function getSchemeUri()
    {
        return $this->_schemeUri;
    }
    /**
     * Sets the optional scheme address to allow for the option of directing
     * the user to a web-page if they do not have the scheme installed on their
     * system. It is an absolute URI.
     * @param string $schemeUri The scheme URI.
     */
    public function setSchemeUri($schemeUri): void
    {
        $this->_schemeUri = $schemeUri;
        if ($schemeUri === null) {
            $this->setFlags(0);
        } else {
            $this->setFlags(1);
        }
    }
    /**
     * Returns the box heap size in bytes.
    *
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 8 +
            ($this->hasFlag(1) ? strlen($this->_schemeUri) + 1 : 0);
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->write($this->_schemeType);
        $writer->writeUInt32BE($this->_schemeVersion);
        if ($this->hasFlag(1)) {
            $writer->writeString8($this->_schemeUri, 1);
        }
    }
}
