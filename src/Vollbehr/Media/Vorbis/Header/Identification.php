<?php

declare(strict_types=1);

namespace Vollbehr\Media\Vorbis\Header;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The identication header is a short header of only a few fields used to declare the stream definitively as Vorbis,
 * and provide a few externally relevant pieces of information about the audio stream.
 * @author Sven Vollbehr
 */
final class Identification extends \Vollbehr\Media\Vorbis\Header
{
    /** @var integer */
    private $_vorbisVersion;

    /** @var integer */
    private $_audioChannels;

    /** @var integer */
    private $_audioSampleRate;

    /** @var integer */
    private $_bitrateMaximum;

    /** @var integer */
    private $_bitrateNominal;

    /** @var integer */
    private $_bitrateMinimum;

    private readonly int $_blocksize0;

    private readonly int $_blocksize1;

    /**
     * Constructs the class with given parameters.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_vorbisVersion   = $this->_reader->readUInt32LE();
        $this->_audioChannels   = $this->_reader->readUInt8();
        $this->_audioSampleRate = $this->_reader->readUInt32LE();
        $this->_bitrateMaximum  = $this->_reader->readInt32LE();
        $this->_bitrateNominal  = $this->_reader->readInt32LE();
        $this->_bitrateMinimum  = $this->_reader->readInt32LE();
        $this->_blocksize0      = 2 ** ($tmp = $this->_reader->readUInt8() & 0xf);
        $this->_blocksize1      = 2 ** ($tmp >> 4 & 0xf);
        $framingFlag            = $this->_reader->readUInt8() & 0x1;
        if ($this->_blocksize0 > $this->_blocksize1 || $framingFlag == 0) {

            throw new \Vollbehr\Media\Vorbis\Exception('Undecodable Vorbis stream');
        }
    }

    /**
     * Returns the vorbis version.
     * @return integer
     */
    public function getVorbisVersion()
    {
        return $this->_vorbisVersion;
    }

    /**
     * Returns the number of audio channels.
     * @return integer
     */
    public function getAudioChannels()
    {
        return $this->_audioChannels;
    }

    /**
     * Returns the audio sample rate.
     * @return integer
     */
    public function getAudioSampleRate()
    {
        return $this->_audioSampleRate;
    }

    /**
     * Returns the maximum bitrate.
     * @return integer
     */
    public function getBitrateMaximum()
    {
        return $this->_bitrateMaximum;
    }

    /**
     * Returns the nominal bitrate.
     * @return integer
     */
    public function getBitrateNominal()
    {
        return $this->_bitrateNominal;
    }

    /**
     * Returns the minimum bitrate.
     * @return integer
     */
    public function getBitrateMinimum()
    {
        return $this->_bitrateMinimum;
    }

    /**
     * Returns the first block size. Allowed final blocksize values are 64, 128, 256, 512, 1024, 2048, 4096 and 8192 in
     * Vorbis I.
     */
    public function getBlocksize1(): int
    {
        return $this->_blocksize1;
    }

    /**
     * Returns the second block size. Allowed final blocksize values are 64, 128, 256, 512, 1024, 2048, 4096 and 8192 in
     * Vorbis I.
     * @return integer
     */
    public function getBlocksize2()
    {
        return $this->_blocksize2;
    }
}
