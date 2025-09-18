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
 * The _File Type Box_ is placed as early as possible in the file (e.g.
 * after any obligatory signature, but before any significant variable-size
 * boxes such as a {@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box},
 * {@see \Vollbehr\Media\Iso14496\Box\Mdat Media Data Box}, or
 * {@see \Vollbehr\Media\Iso14496\Box\Free Free Space}). It identifies which
 * specification is the _best use_ of the file, and a minor version of
 * that specification; and also a set of others specifications to which the
 * file complies.
 * The minor version is informative only. It does not appear for
 * compatible-brands, and must not be used to determine the conformance of a
 * file to a standard. It may allow more precise identification of the major
 * specification, for inspection, debugging, or improved decoding.
 * The type _isom_ (ISO Base Media file) is defined as identifying files
 * that conform to the first version of the ISO Base Media File Format. More
 * specific identifiers can be used to identify precise versions of
 * specifications providing more detail. This brand is not be used as the major
 * brand; this base file format should be derived into another specification to
 * be used. There is therefore no defined normal file extension, or mime type
 * assigned to this brand, nor definition of the minor version when _isom_
 * is the major brand.
 * Files would normally be externally identified (e.g. with a file extension or
 * mime type) that identifies the _best use_ (major brand), or the brand
 * that the author believes will provide the greatest compatibility.
 * The brand _iso2_ shall be used to indicate compatibility with the
 * amended version of the ISO Base Media File Format; it may be used in addition
 * to or instead of the _isom_ brand and the same usage rules apply. If
 * used without the brand _isom_ identifying the first version of the
 * specification, it indicates that support for some or all of the technology
 * introduced by the amended version of the ISO Base Media File Format is
 * required.
 * The brand _avc1_ shall be used to indicate that the file is conformant
 * with the _AVC Extensions_. If used without other brands, this implies
 * that support for those extensions is required. The use of _avc1_ as a
 * major-brand may be permitted by specifications; in that case, that
 * specification defines the file extension and required behavior.
 * If a Meta-box with an MPEG-7 handler type is used at the file level, then the
 * brand _mp71_ is a member of the compatible-brands list in the file-type
 * box.
 * @author Sven Vollbehr
 */
final class Ftyp extends \Vollbehr\Media\Iso14496\Box
{
    /** @var integer */
    private $_majorBrand;
    /** @var integer */
    private $_minorVersion;
    /** @var integer */
    private $_compatibleBrands = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);

        $this->_majorBrand   = $this->_reader->readString8(4);
        $this->_minorVersion = $this->_reader->readUInt32BE();
        while ($this->_reader->getOffset() < $this->getSize()) {
            if (($brand = $this->_reader->readString8(4)) != '') {
                $this->_compatibleBrands[] = $brand;
            }
        }
    }
    /**
     * Returns the major version brand.
    *
     * @return string
     */
    public function getMajorBrand()
    {
        return $this->_majorBrand;
    }

    /**
     * Sets the major version brand.
     * @param string $majorBrand The major version brand.
     */
    public function setMajorBrand($majorBrand): void
    {
        $this->_majorBrand = $majorBrand;
    }
    /**
     * Returns the minor version number.
     * @return integer
     */
    public function getMinorVersion()
    {
        return $this->_minorVersion;
    }
    /**
     * Sets the minor version number.
     * @param integer $minorVersion The minor version number.
     */
    public function setMinorVersion($minorVersion): void
    {
        $this->_minorVersion = $minorVersion;
    }
    /**
     * Returns the array of compatible version brands.
     * @return Array
     */
    public function getCompatibleBrands()
    {
        return $this->_compatibleBrands;
    }
    /**
     * Sets the array of compatible version brands.
     * @param Array $compatibleBrands The array of compatible version brands.
     */
    public function setCompatibleBrands($compatibleBrands): void
    {
        $this->_compatibleBrands = $compatibleBrands;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 8 + 4 * count($this->_compatibleBrands);
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeString8(substr($this->_majorBrand, 0, 4))
               ->writeUInt32BE($this->_minorVersion);
        $counter = count($this->_compatibleBrands);
        for ($i = 0; $i < $counter; $i++) {
            $writer->writeString8(substr((string) $this->_compatibleBrands[$i], 0, 4));
        }
    }
}
