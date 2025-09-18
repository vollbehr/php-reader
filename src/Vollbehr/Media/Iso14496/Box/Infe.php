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
 * The _Item Information Entry Box_ contains the entry information.
 * @author Sven Vollbehr
 */
final class Infe extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_itemId;
    /** @var integer */
    private $_itemProtectionIndex;
    /** @var string */
    private $_itemName;
    /** @var string */
    private $_contentType;
    /** @var string */
    private $_contentEncoding;

    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_itemId                                                   = $this->_reader->readUInt16BE();
        $this->_itemProtectionIndex                                      = $this->_reader->readUInt16BE();
        [$this->_itemName, $this->_contentType, $this->_contentEncoding] = preg_split('/\\x00/', (string) $this->_reader->read($this->getOffset() + $this->getSize() -
                  $this->_reader->getOffset()));
    }
    /**
     * Returns the item identifier. The value is either 0 for the primary
     * resource (e.g. the XML contained in an
     * {@see \Vollbehr\Media\Iso14496\Box\Xml XML Box}) or the ID of the item for
     * which the following information is defined.
     * @return integer
     */
    public function getItemId()
    {
        return $this->_itemId;
    }
    /**
     * Sets the item identifier. The value must be either 0 for the primary
     * resource (e.g. the XML contained in an
     * {@see \Vollbehr\Media\Iso14496\Box\Xml XML Box}) or the ID of the item for
     * which the following information is defined.
     * @param integer $itemId The item identifier.
     */
    public function setItemId($itemId): void
    {
        $this->_itemId = $itemId;
    }
    /**
     * Returns the item protection index. The value is either 0 for an
     * unprotected item, or the one-based index into the
     * {@see \Vollbehr\Media\Iso14496\Box\Ipro Item Protection Box} defining the
     * protection applied to this item (the first box in the item protection box
     * has the index 1).
     * @return integer
     */
    public function getItemProtectionIndex()
    {
        return $this->_itemProtectionIndex;
    }
    /**
     * Sets the item protection index. The value must be either 0 for an
     * unprotected item, or the one-based index into the
     * {@see \Vollbehr\Media\Iso14496\Box\Ipro Item Protection Box} defining the
     * protection applied to this item (the first box in the item protection box
     * has the index 1).
     * @param integer $itemProtectionIndex The index.
     */
    public function setItemProtectionIndex($itemProtectionIndex): void
    {
        $this->_itemProtectionIndex = $itemProtectionIndex;
    }
    /**
     * Returns the symbolic name of the item.
     * @return string
     */
    public function getItemName()
    {
        return $this->_itemName;
    }
    /**
     * Sets the symbolic name of the item.
     * @param string $itemName The item name.
     */
    public function setItemName($itemName): void
    {
        $this->_itemName = $itemName;
    }
    /**
     * Returns the MIME type for the item.
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }
    /**
     * Sets the MIME type for the item.
     * @param string $contentType The content type.
     */
    public function setContentType($contentType): void
    {
        $this->_contentType = $contentType;
    }
    /**
     * Returns the optional content encoding type as defined for
     * Content-Encoding for HTTP /1.1. Some possible values are _gzip_,
     * _compress_ and _deflate_. An empty string indicates no content
     * encoding.
     * @return string
     */
    public function getContentEncoding()
    {
        return $this->_contentEncoding;
    }
    /**
     * Sets the optional content encoding type as defined for
     * Content-Encoding for HTTP /1.1. Some possible values are _gzip_,
     * _compress_ and _deflate_. An empty string indicates no content
     * encoding.
     * @param string $contentEncoding The content encoding.
     */
    public function setContentEncoding($contentEncoding): void
    {
        $this->_contentEncoding = $contentEncoding;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 7 + strlen($this->_itemName) +
            strlen($this->_contentType) + strlen($this->_contentEncoding);
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt16BE($this->_itemId)
               ->writeUInt16BE($this->_itemProtectionIndex)
               ->writeString8($this->_itemName, 1)
               ->writeString8($this->_contentType, 1)
               ->writeString8($this->_contentEncoding, 1);
    }
}
