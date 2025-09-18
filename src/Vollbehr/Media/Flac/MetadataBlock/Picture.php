<?php

declare(strict_types=1);

namespace Vollbehr\Media\Flac\MetadataBlock;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents the picture metadata block. This block is for storing pictures associated with the file, most
 * commonly cover art from CDs. There may be more than one PICTURE block in a file. The picture format is similar to the
 * {@see \Vollbehr\Media\Id3v2\Frame\Apic APIC} frame in {@see \Vollbehr\Media\Id3v2 ID3v2}. The PICTURE block has a type,
 * MIME type, and UTF-8 description like {@see \Vollbehr\Media\Id3v2 ID3v2}, and supports external linking via URL (though
 * this is discouraged). The differences are that there is no uniqueness constraint on the description field, and the
 * MIME type is mandatory. The FLAC PICTURE block also includes the resolution, color depth, and palette size so that
 * the client can search for a suitable picture without having to scan them all.
 * @author Sven Vollbehr
 */
final class Picture extends \Vollbehr\Media\Flac\MetadataBlock
{
    /**
     * The list of picture types.
     * @var Array
     */
    public static $types = ['Other', '32x32 pixels file icon (PNG only)', 'Other file icon',
         'Cover (front)', 'Cover (back)', 'Leaflet page',
         'Media (e.g. label side of CD)', 'Lead artist/lead performer/soloist',
         'Artist/performer', 'Conductor', 'Band/Orchestra', 'Composer',
         'Lyricist/text writer', 'Recording Location', 'During recording',
         'During performance', 'Movie/video screen capture',
         'A bright coloured fish', 'Illustration', 'Band/artist logotype',
         'Publisher/Studio logotype'];

    /** @var string */
    private $_mimeType;

    /** @var string */
    private $_description;

    /** @var integer */
    private $_width;

    /** @var integer */
    private $_height;

    /** @var integer */
    private $_colorDepth;

    /** @var integer */
    private $_numberOfColors;

    /** @var integer */
    private $_dataSize;

    /** @var string */
    private $_data;

    /**
     * Constructs the class with given parameters and parses object related data.
     * @todo  There is the possibility to put only a link to the picture file by
     *  using the MIME type '-->' and having a complete URL instead of picture
     *  data. Support for such needs design considerations.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_mimeType       = $this->_reader->read($this->_reader->readUInt32BE());
        $this->_description    = $this->_reader->read($this->_reader->readUInt32BE());
        $this->_width          = $this->_reader->readUInt32BE();
        $this->_height         = $this->_reader->readUInt32BE();
        $this->_colorDepth     = $this->_reader->readUInt32BE();
        $this->_numberOfColors = $this->_reader->readUInt32BE();
        $this->_data           = $this->_reader->read($this->_dataSize = $this->_reader->readUInt32BE());
    }

    /**
     * Returns the picture type.
     * @return integer
     */
    public function getPictureType()
    {
        return $this->_pictureType;
    }

    /**
     * Returns the MIME type.
     * @return string
     */
    public function getMimeType()
    {
        return $this->_mimeType;
    }

    /**
     * Returns the picture description.
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Returns the picture width.
     * @return integer
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Returns the picture height.
     * @return integer
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Returns the color depth of the picture in bits-per-pixel.
     * @return integer
     */
    public function getColorDepth()
    {
        return $this->_colorDepth;
    }

    /**
     * Returns the number of colors used for indexed-color pictures, or 0 for non-indexed pictures.
     * @return integer
     */
    public function getNumberOfColors()
    {
        return $this->_numberOfColors;
    }

    /**
     * Returns the picture data size.
     * @return integer
     */
    public function getDataSize()
    {
        return $this->_dataSize;
    }

    /**
     * Returns the picture data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
}
