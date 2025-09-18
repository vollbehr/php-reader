<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The first part of the ID3v2 tag is the 10 byte tag header. The header
 * contains information about the tag version and options.
 * @author Sven Vollbehr
 */
final class Header extends BaseObject
{
    /** A flag to denote whether or not unsynchronisation is applied on all
            frames */
    public const UNSYNCHRONISATION = 128;

    /** A flag to denote whether or not the header is followed by an extended
            header */
    public const EXTENDED_HEADER = 64;

    /** A flag used as an experimental indicator. This flag shall always be set
            when the tag is in an experimental stage. */
    public const EXPERIMENTAL = 32;

    /**
     * A flag to denote whether a footer is present at the very end of the tag.
     * @since ID3v2.4.0
     */
    public const FOOTER = 16;
    private int | float $_version;
    /** @var integer */
    private $_flags = 0;

    /** @var integer */
    private $_size;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ID3v2 tag.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $this->_version = $options['version'] = $this->_reader->readUInt8() + $this->_reader->readUInt8() / 10;
        $this->_flags   = $this->_reader->readUInt8();
        $this->_size    = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
    }

    /**
     * Returns the tag version number. The version number is in the form of
     * major.revision.
     * @return integer
     */
    public function getVersion(): float | int
    {
        return $this->_version;
    }

    /**
     * Sets the tag version number. Supported version numbers are 3.0 and 4.0
     * for ID3v2.3.0 and ID3v2.4.0 standards, respectively.
     * @param integer $version The tag version number in the form of
     *                major.revision.
     */
    public function setVersion(float | int $version): void
    {
        $this->setOption('version', $this->_version = $version);
    }

    /**
     * Checks whether or not the flag is set. Returns <var>true</var> if the
     * flag is set, <var>false</var> otherwise.
     * @param integer $flag The flag to query.
     */
    public function hasFlag($flag): bool
    {
        return ($this->_flags & $flag) == $flag;
    }

    /**
     * Returns the flags byte.
     * @return integer
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * Sets the flags byte.
     * @param string $flags The flags byte.
     */
    public function setFlags($flags): void
    {
        $this->_flags = $flags;
    }

    /**
     * Returns the tag size, excluding the header and the footer.
     * @return integer
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Sets the tag size, excluding the header and the footer. Called
     * automatically upon tag generation to adjust the tag size.
     * @param integer $size The size of the tag, in bytes.
     */
    public function setSize($size): void
    {
        $this->_size = $size;
    }

    /**
     * Writes the header/footer data without the identifier.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $writer->writeUInt8(floor($this->_version))
               ->writeUInt8(($this->_version - floor($this->_version)) * 10)
               ->writeUInt8($this->_flags)
               ->writeUInt32BE($this->encodeSynchsafe32($this->_size));
    }
}
