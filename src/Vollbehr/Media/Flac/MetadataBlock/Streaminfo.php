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
 * This class represents the streaminfo metadata block. This block has information about the whole stream, like sample
 * rate, number of channels, total number of samples, etc. It must be present as the first metadata block in the stream.
 * Other metadata blocks may follow, and ones that the decoder doesn't understand, it will skip.
 * @author Sven Vollbehr
 */
final class Streaminfo extends \Vollbehr\Media\Flac\MetadataBlock
{
    /** @var integer */
    private $_minimumBlockSize;

    /** @var integer */
    private $_maximumBlockSize;

    /** @var integer */
    private $_minimumFrameSize;

    /** @var integer */
    private $_maximumFrameSize;

    private readonly float | int $_sampleRate;

    private readonly float | int $_numberOfChannels;

    private readonly float | int $_bitsPerSample;

    private readonly int $_numberOfSamples;

    private readonly string $_md5Signature;

    /**
     * Constructs the class with given parameters and parses object related data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_minimumBlockSize = $this->_reader->readUInt16BE();
        $this->_maximumBlockSize = $this->_reader->readUInt16BE();
        $this->_minimumFrameSize = $this->_reader->readUInt24BE();
        $this->_maximumFrameSize = $this->_reader->readUInt24BE();
        $this->_sampleRate       = \Vollbehr\Bit\Twiddling::getValue(($tmp = $this->_reader->readUInt32BE()), 12, 31);
        $this->_numberOfChannels = \Vollbehr\Bit\Twiddling::getValue($tmp, 9, 11) + 1;
        $this->_bitsPerSample    = \Vollbehr\Bit\Twiddling::getValue($tmp, 4, 8) + 1;
        $this->_numberOfSamples  = (\Vollbehr\Bit\Twiddling::getValue($tmp, 0, 3) << 32) | $this->_reader->readUInt32BE();
        $this->_md5Signature     = bin2hex($this->_reader->read(16));
    }

    /**
     * Returns the minimum block size (in samples) used in the stream.
     * @return integer
     */
    public function getMinimumBlockSize()
    {
        return $this->_minimumBlockSize;
    }

    /**
     * Returns the maximum block size (in samples) used in the stream. (Minimum blocksize == maximum blocksize) implies
     * a fixed-blocksize stream.
     * @return integer
     */
    public function getMaximumBlockSize()
    {
        return $this->_maximumBlockSize;
    }

    /**
     * Returns the minimum frame size (in bytes) used in the stream. May be 0 to imply the value is not known.
     * @return integer
     */
    public function getMinimumFrameSize()
    {
        return $this->_minimumFrameSize;
    }

    /**
     * Returns the maximum frame size (in bytes) used in the stream. May be 0 to imply the value is not known.
     * @return integer
     */
    public function getMaximumFrameSize()
    {
        return $this->_maximumFrameSize;
    }

    /**
     * Returns sample rate in Hz. The maximum sample rate is limited by the structure of frame headers to 655350Hz.
     * Also, a value of 0 is invalid.
     * @return integer
     */
    public function getSampleRate(): int | float
    {
        return $this->_sampleRate;
    }

    /**
     * Returns number of channels. FLAC supports from 1 to 8 channels.
     * @return integer
     */
    public function getNumberOfChannels(): int | float
    {
        return $this->_numberOfChannels;
    }

    /**
     * Returns bits per sample. FLAC supports from 4 to 32 bits per sample. Currently the reference encoder and
     * decoders only support up to 24 bits per sample.
     * @return integer
     */
    public function getBitsPerSample(): int | float
    {
        return $this->_bitsPerSample;
    }

    /**
     * Returns total samples in stream. 'Samples' means inter-channel sample, i.e. one second of 44.1Khz audio will
     * have 44100 samples regardless of the number of channels. A value of zero here means the number of total samples
     * is unknown.
     */
    public function getNumberOfSamples(): int
    {
        return $this->_numberOfSamples;
    }

    /**
     * Returns MD5 signature of the unencoded audio data. This allows the decoder to determine if an error exists in
     * the audio data even when the error does not result in an invalid bitstream.
     */
    public function getMd5Signature(): string
    {
        return $this->_md5Signature;
    }
}
