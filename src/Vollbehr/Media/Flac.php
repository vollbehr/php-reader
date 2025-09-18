<?php

declare(strict_types=1);

namespace Vollbehr\Media;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents a file FLAC file format as described in {@see http://flac.sourceforge.net/format.html}. FLAC
 * stands for Free Lossless Audio Codec, an audio format similar to MP3, but lossless, meaning that audio is compressed
 * in FLAC without any loss in quality. This is similar to how Zip works, except with FLAC you will get much better
 * compression because it is designed specifically for audio, and you can play back compressed FLAC files in your
 * favorite player (or your car or home stereo, see supported devices) just like you would an MP3 file.
 * FLAC stands out as the fastest and most widely supported lossless audio codec, and the only one that at once is
 * non-proprietary, is unencumbered by patents, has an open-source reference implementation, has a well documented
 * format and API, and has several other independent implementations.
 * @author Sven Vollbehr
 */
final class Flac
{
    /**
     * The streaminfo metadata block
     */
    public const STREAMINFO = 0;

    /**
     * The padding metadata block
     */
    public const PADDING = 1;

    /**
     * The application metadata block
     */
    public const APPLICATION = 2;

    /**
     * The seektable metadata block
     */
    public const SEEKTABLE = 3;

    /**
     * The vorbis comment metadata block
     */
    public const VORBIS_COMMENT = 4;

    /**
     * The cuesheet metadata block
     */
    public const CUESHEET = 5;

    /**
     * The picture metadata block
     */
    public const PICTURE = 6;

    private ?\Vollbehr\Io\FileReader $_reader;
    private array $_metadataBlocks = [];

    /**
     * Constructs the class with given filename.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file,
     *  file descriptor of an opened file, or a {@see \Vollbehr\Io\Reader} instance.
     * @throws \Vollbehr\Io\Exception if an error occur in stream handling.
     * @throws Flac\Exception if an error occurs in vorbis bitstream reading.
     */
    public function __construct($filename)
    {
        if ($filename instanceof \Vollbehr\Io\Reader) {
            $this->_reader = &$filename;
        } else {
            try {
                $this->_reader = new \Vollbehr\Io\FileReader($filename);
            } catch (\Vollbehr\Io\Exception $e) {
                $this->_reader = null;

                throw new Flac\Exception($e->getMessage());
            }
        }

        $capturePattern = $this->_reader->read(4);
        if ($capturePattern != 'fLaC') {

            throw new Flac\Exception('Not a valid FLAC bitstream');
        }

        while (true) {
            $offset = $this->_reader->getOffset();
            $last   = ($tmp = $this->_reader->readUInt8()) >> 7 & 0x1;
            $type   = $tmp & 0x7f;
            $size   = $this->_reader->readUInt24BE();

            $this->_reader->setOffset($offset);
            switch ($type) {
                case self::STREAMINFO:     // 0

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\Streaminfo($this->_reader);
                    break;
                case self::PADDING:        // 1

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\Padding($this->_reader);
                    break;
                case self::APPLICATION:    // 2

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\Application($this->_reader);
                    break;
                case self::SEEKTABLE:      // 3

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\Seektable($this->_reader);
                    break;
                case self::VORBIS_COMMENT: // 4

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\VorbisComment($this->_reader);
                    break;
                case self::CUESHEET:       // 5

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\Cuesheet($this->_reader);
                    break;
                case self::PICTURE:        // 6

                    $this->_metadataBlocks[] = new Flac\MetadataBlock\Picture($this->_reader);
                    break;
                default:
                    // break intentionally omitted
            }
            $this->_reader->setOffset($offset + 4 /* header */ + $size);

            // Jump off the loop if we reached the end of metadata blocks
            if ($last === 1) {
                break;
            }
        }
    }

    /**
     * Checks whether the given metadata block is there. Returns <var>true</var> if one ore more frames are present,
     * <var>false</var> otherwise.
     * @param string $type The metadata block type.
     */
    public function hasMetadataBlock($type): bool
    {
        $metadataBlockCount = count($this->_metadataBlocks);
        for ($i = 0; $i < $metadataBlockCount; $i++) {
            if ($this->_metadataBlocks[$i]->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all the metadata blocks as an associate array.
     */
    public function getMetadataBlocks(): array
    {
        return $this->_metadataBlocks;
    }

    /**
     * Returns an array of metadata blocks frames matching the given type or an empty array if no metadata blocks
     * matched the type.
     * Please note that one may also use the shorthand $obj->type or $obj->getType(), where the type is the metadata
     * block name, to access the first metadata block with the given type.
     * @param string $type The metadata block type.
     */
    public function getMetadataBlocksByType($type): array
    {
        $matches            = [];
        $metadataBlockCount = count($this->_metadataBlocks);
        for ($i = 0; $i < $metadataBlockCount; $i++) {
            if ($this->_metadataBlocks[$i]->getType() === $type) {
                $matches[] = $this->_metadataBlocks[$i];
            }
        }

        return $matches;
    }

    /**
     * Magic function so that $obj->X() or $obj->getX() will work, where X is the name of the metadata block. If there
     * is no metadata block by the given name, an exception is thrown.
     * @param string $name The metadata block name.
     */
    public function __call($name, $arguments)
    {
        if (preg_match('/^(?:get)([A-Z].*)$/', $name, $matches)) {
            $name = lcfirst($matches[1]);
        }
        if (defined($constant = 'self::' . strtoupper((string) preg_replace('/(?<=[a-z])[A-Z]/', '_$0', $name)))) {
            $metadataBlocks = $this->getMetadataBlocksByType(constant($constant));
            if (isset($metadataBlocks[0])) {
                return $metadataBlocks[0];
            }
        }
        if (!empty($this->_comments[strtoupper($name)])) {
            return $this->_comments[strtoupper($name)][0];
        }

        throw new Flac\Exception('Unknown metadata block: ' . strtoupper($name));
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The metadata block name.
     */
    public function __get(string $name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        }
        if (defined($constant = 'self::' . strtoupper((string) preg_replace('/(?<=[a-z])[A-Z]/', '_$0', $name)))) {
            $metadataBlocks = $this->getMetadataBlocksByType(constant($constant));
            if (isset($metadataBlocks[0])) {
                return $metadataBlocks[0];
            }
        }

        throw new Flac\Exception('Unknown metadata block or field: ' . $name);
    }

    /**
     * Magic function so that assignments with $obj->value will work.
     * @param string $name  The field name.
     * @param string $value The field value.
     */
    public function __set(string $name, $value)
    {
        if (method_exists($this, 'set' . ucfirst(strtolower($name)))) {
            call_user_func([$this, 'set' . ucfirst(strtolower($name))], $value);
        } else {

            throw new Flac\Exception('Unknown field: ' . $name);
        }
    }
}
