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
 * When the primary data is in XML format and it is desired that the XML be
 * stored directly in the meta-box, one of the _XML Box_ forms may be used.
 * The Binary XML Box may only be used when there is a single well-defined
 * binarization of the XML for that defined format as identified by the handler.
 * Within an XML box the data is in UTF-8 format unless the data starts with a
 * byte-order-mark (BOM), which indicates that the data is in UTF-16 format.
 * @author Sven Vollbehr
 */
final class Bxml extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var string */
    private $_xml;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_xml = $this->_reader->read($this->getOffset() + $this->getSize() -
             $this->_reader->getOffset());
    }

    /**
     * Returns the XML data.
     * @return string
     */
    public function getXml()
    {
        return $this->_xml;
    }
    /**
     * Sets the binary data.
     * @param string $xml The XML data.
     */
    public function setXml($xml): void
    {
        $this->_xml = $xml;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() + strlen($this->_xml);
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->write($this->_xml);
    }
}
