<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf\Object;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Content Branding Object_ stores branding data for an ASF file,
 * including information about a banner image and copyright associated with the
 * file.
 * @author Sven Vollbehr
 */
final class ContentBranding extends \Vollbehr\Media\Asf\BaseObject
{
    /**
     * Indicates that there is no banner
     */
    public const TYPE_NONE = 0;

    /**
     * Indicates that the data represents a bitmap
     */
    public const TYPE_BMP = 1;

    /**
     * Indicates that the data represents a JPEG
     */
    public const TYPE_JPEG = 2;

    /**
     * Indicates that the data represents a GIF
     */
    public const TYPE_GIF = 3;

    /** @var integer */
    private $_bannerImageType;

    /** @var string */
    private $_bannerImageData;

    /** @var string */
    private $_bannerImageUrl;

    /** @var string */
    private $_copyrightUrl;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_bannerImageType = $this->_reader->readUInt32LE();
        $bannerImageDataSize    = $this->_reader->readUInt32LE();
        $this->_bannerImageData = $this->_reader->read($bannerImageDataSize);
        $bannerImageUrlLength   = $this->_reader->readUInt32LE();
        $this->_bannerImageUrl  = $this->_reader->read($bannerImageUrlLength);
        $copyrightUrlLength     = $this->_reader->readUInt32LE();
        $this->_copyrightUrl    = $this->_reader->read($copyrightUrlLength);
    }

    /**
     * Returns the type of data contained in the _Banner Image Data_. Valid
     * values are 0 to indicate that there is no banner image data; 1 to
     * indicate that the data represent a bitmap; 2 to indicate that the data
     * represents a JPEG; and 3 to indicate that the data represents a GIF. If
     * this value is set to 0, then the _Banner Image Data Size field is set
     * to 0, and the _Banner Image Data_ field is empty.
     * @return integer
     */
    public function getBannerImageType()
    {
        return $this->_bannerImageType;
    }

    /**
     * Sets the type of data contained in the _Banner Image Data_. Valid
     * values are 0 to indicate that there is no banner image data; 1 to
     * indicate that the data represent a bitmap; 2 to indicate that the data
     * represents a JPEG; and 3 to indicate that the data represents a GIF. If
     * this value is set to 0, then the _Banner Image Data Size field is set
     * to 0, and the _Banner Image Data_ field is empty.
     * @param integer $bannerImageType The type of data.
     */
    public function setBannerImageType($bannerImageType): void
    {
        $this->_bannerImageType = $bannerImageType;
    }

    /**
     * Returns the entire banner image, including the header for the appropriate
     * image format.
     * @return string
     */
    public function getBannerImageData()
    {
        return $this->_bannerImageData;
    }

    /**
     * Sets the entire banner image, including the header for the appropriate
     * image format.
     * @param string $bannerImageData The entire banner image.
     */
    public function setBannerImageData($bannerImageData): void
    {
        $this->_bannerImageData = $bannerImageData;
    }

    /**
     * Returns, if present, a link to more information about the banner image.
     * @return string
     */
    public function getBannerImageUrl()
    {
        return $this->_bannerImageUrl;
    }

    /**
     * Sets a link to more information about the banner image.
     * @param string $bannerImageUrl The link.
     */
    public function setBannerImageUrl($bannerImageUrl): void
    {
        $this->_bannerImageUrl = $bannerImageUrl;
    }

    /**
     * Returns, if present, a link to more information about the copyright for
     * the content.
     * @return string
     */
    public function getCopyrightUrl()
    {
        return $this->_copyrightUrl;
    }

    /**
     * Sets a link to more information about the copyright for the content.
     * @param string $copyrightUrl The copyright link.
     */
    public function setCopyrightUrl($copyrightUrl): void
    {
        $this->_copyrightUrl = $copyrightUrl;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $buffer = new \Vollbehr\Io\StringWriter();
        $buffer->writeUInt32LE($this->_bannerImageType)
               ->writeUInt32LE(count($this->_bannerImageData))
               ->write($this->_bannerImageData)
               ->writeUInt32LE(count($this->_bannerImageUrl))
               ->write($this->_bannerImageUrl)
               ->writeUInt32LE(count($this->_copyrightUrl))
               ->write($this->_copyrightUrl);

        $this->setSize(24 /* for header */ + $buffer->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->write($buffer->toString());
    }
}
