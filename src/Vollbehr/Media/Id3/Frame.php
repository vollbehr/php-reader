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
 * A base class for all ID3v2 frames as described in the
 * {@see http://www.id3.org/id3v2.4.0-frames ID3v2 frames document}.
 * @author Sven Vollbehr
 */
abstract class Frame extends BaseObject
{
    /**
     * This flag tells the tag parser what to do with this frame if it
     * unknown and the tag is altered in any way. This applies to all kinds of
     * alterations, including adding more padding and reordering the frames.
     */
    public const DISCARD_ON_TAGCHANGE = 16384;
    /**
     * This flag tells the tag parser what to do with this frame if it is
     * unknown and the file, excluding the tag, is altered. This does not apply
     * when the audio is completely replaced with other audio data.
     */
    public const DISCARD_ON_FILECHANGE = 8192;
    /**
     * This flag, if set, tells the software that the contents of this frame are
     * intended to be read only. Changing the contents might break something,
     * e.g. a signature.
     */
    public const READ_ONLY = 4096;
    /**
     * This flag indicates whether or not this frame belongs in a group with
     * other frames. If set, a group identifier byte is added to the frame.
     * Every frame with the same group identifier belongs to the same group.
     */
    public const GROUPING_IDENTITY = 32;
    /**
     * This flag indicates whether or not the frame is compressed. A _Data
     * Length Indicator_ byte is included in the frame.
     * @see DATA_LENGTH_INDICATOR
     */
    public const COMPRESSION = 8;
    /**
     * This flag indicates whether or not the frame is encrypted. If set, one
     * byte indicating with which method it was encrypted will be added to the
     * frame. See description of the {@see \Vollbehr\Media\Id3\Frame\Encr ENCR}
     * frame for more information about encryption method registration.
     * Encryption should be done after compression. Whether or not setting this
     * flag requires the presence of a _Data Length Indicator_ depends on
     * the specific algorithm used.
     * @see DATA_LENGTH_INDICATOR
     */
    public const ENCRYPTION = 4;
    /**
     * This flag indicates whether or not unsynchronisation was applied to this
     * frame.
     * @since ID3v2.4.0
     */
    public const UNSYNCHRONISATION = 2;
    /**
     * This flag indicates that a data length indicator has been added to the
     * frame.
     * @since ID3v2.4.0
     */
    public const DATA_LENGTH_INDICATOR = 1;
    /** @var integer */
    private $_identifier;

    /** @var integer */
    private $_size = 0;

    /** @var integer */
    private $_flags = 0;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ID3v2 tag.
     * Replaces the reader instance with a string reader object instance that
     * can be used to further process the data in the frame sub class.
     * @todo  Only limited subset of flags are processed.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            $this->_identifier = strtoupper(substr(static::class, -4));
        } else {
            $this->_identifier = strtoupper($this->_reader->readString8(4, ' '));

            /* ID3v2.3.0 size and flags; convert flags to 2.4.0 format */
            if ($this->getOption('version', 4) < 4) {
                $this->_size = $this->_reader->readUInt32BE();
                $flags       = $this->_reader->readUInt16BE();
                if (($flags & 0x8000) == 0x8000) {
                    $this->_flags |= self::DISCARD_ON_TAGCHANGE;
                }
                if (($flags & 0x4000) == 0x4000) {
                    $this->_flags |= self::DISCARD_ON_FILECHANGE;
                }
                if (($flags & 0x2000) == 0x2000) {
                    $this->_flags |= self::READ_ONLY;
                }
                if (($flags & 0x80) == 0x80) {
                    $this->_flags |= self::COMPRESSION;
                }
                if (($flags & 0x40) == 0x40) {
                    $this->_flags |= self::ENCRYPTION;
                }
                if (($flags & 0x20) == 0x20) {
                    $this->_flags |= self::GROUPING_IDENTITY;
                }
            }

            /* ID3v2.4.0 size and flags */ else {
                $this->_size  = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
                $this->_flags = $this->_reader->readUInt16BE();
            }

            $dataLength = $this->_size;
            if ($this->hasFlag(self::DATA_LENGTH_INDICATOR)) {
                $dataLength = $this->decodeSynchsafe32($this->_reader->readUInt32BE());
                $this->_size -= 4;
            }

            $data        = $this->_reader->read($this->_size);
            $this->_size = $dataLength;

            if ($this->hasFlag(self::UNSYNCHRONISATION) ||
                $this->getOption('unsynchronisation', false) === true) {
                $data = $this->decodeUnsynchronisation($data);
            }

            $this->_reader = new \Vollbehr\Io\StringReader($data);
        }
    }

    /**
     * Returns the frame identifier string.
     * @return string
     */
    final public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Sets the frame identifier.
     * @param string $identifier The identifier.
     */
    final public function setIdentifier($identifier): void
    {
        $this->_identifier = $identifier;
    }

    /**
     * Returns the size of the data in the final frame, after encryption,
     * compression and unsynchronisation. The size is excluding the frame
     * header.
     * @return integer
     */
    final public function getSize()
    {
        return $this->_size;
    }

    /**
     * Checks whether or not the flag is set. Returns <var>true</var> if the
     * flag is set, <var>false</var> otherwise.
     * @param integer $flag The flag to query.
     * @return boolean
     */
    final public function hasFlag($flag)
    {
        return ($this->_flags & $flag) == $flag;
    }

    /**
     * Returns the frame flags byte.
     * @return integer
     */
    final public function getFlags($flags)
    {
        return $this->_flags;
    }

    /**
     * Sets the frame flags byte.
     * @param string $flags The flags byte.
     */
    final public function setFlags($flags): void
    {
        $this->_flags = $flags;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    abstract protected function _writeData($writer);
    /**
     * Writes the frame data with the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        /* ID3v2.3.0 Flags; convert from 2.4.0 format */
        if ($this->getOption('version', 4) < 4) {
            $flags = 0;
            if ($this->hasFlag(self::DISCARD_ON_TAGCHANGE)) {
                $flags |= 0x8000;
            }
            if ($this->hasFlag(self::DISCARD_ON_FILECHANGE)) {
                $flags |= 0x4000;
            }
            if ($this->hasFlag(self::READ_ONLY)) {
                $flags |= 0x2000;
            }
            if ($this->hasFlag(self::COMPRESSION)) {
                $flags |= 0x80;
            }
            if ($this->hasFlag(self::ENCRYPTION)) {
                $flags |= 0x40;
            }
            if ($this->hasFlag(self::GROUPING_IDENTITY)) {
                $flags |= 0x20;
            }
        }

        /* ID3v2.4.0 Flags */ else {
            $flags = $this->_flags;
        }

        $this->_writeData($buffer = new \Vollbehr\Io\StringWriter());
        $data = $buffer->toString();
        $size = $this->_size = strlen((string) $data);

        // ID3v2.4.0 supports frame level unsynchronisation. The corresponding
        // option is set to true when any of the frames use the
        // unsynchronisation scheme. The usage is denoted by
        // \Vollbehr\Media\Id3\Header flag that is set accordingly upon file write.
        if ($this->getOption('version', 4) >= 4) {
            $data = $this->encodeUnsynchronisation($data);
            if ($dataLength = strlen($data) !== $size) {
                $size = 4 + $dataLength;
                $flags |= self::DATA_LENGTH_INDICATOR | self::UNSYNCHRONISATION;
                $this->setOption('unsynchronisation', true);
            } else {
                $flags &= ~(self::DATA_LENGTH_INDICATOR |
                            self::UNSYNCHRONISATION);
            }
        }

        $writer->writeString8(substr($this->_identifier, 0, 4), 4, ' ')
               ->writeUInt32BE($this->getOption('version', 4) < 4 ? $size : $this->encodeSynchsafe32($size))
               ->writeUInt16BE($flags);

        if (($flags & self::DATA_LENGTH_INDICATOR) ==
                self::DATA_LENGTH_INDICATOR) {
            $writer->writeUInt32BE($this->getOption('version', 4) < 4 ? $this->_size : $this->encodeSynchsafe32($this->_size));
        }
        $writer->write($data);
    }
}
