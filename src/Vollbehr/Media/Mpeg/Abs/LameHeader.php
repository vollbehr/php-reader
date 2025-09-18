<?php

declare(strict_types=1);

namespace Vollbehr\Media\Mpeg\Abs;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * This class represents a LAME extension to the Xing VBR header. The purpose of
 * this header is to provide extra information about the audio bistream, encoder
 * and parameters used. This header should, as much as possible, be meaningfull
 * for as many encoders as possible, even if it is unlikely that other encoders
 * than LAME will implement it.
 * This header should be backward compatible with the Xing VBR tag, providing
 * basic support for a lot of already written software. As much as possible the
 * current revision (revision 1) should provide information similar to the one
 * already provided by revision 0.
 * @author Sven Vollbehr
 */
class LameHeader extends BaseObject
{
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VBR_METHOD_CONSTANT = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VBR_METHOD_ABR = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VBR_METHOD_RH = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VBR_METHOD_MTRH = 4;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const VBR_METHOD_MT = 5;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const ENCODING_FLAG_NSPSYTUNE = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const ENCODING_FLAG_NSSAFEJOINT = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const ENCODING_FLAG_NOGAP_CONTINUED = 4;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const ENCODING_FLAG_NOGAP_CONTINUATION = 8;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_MONO = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_STEREO = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_DUAL = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_JOINT = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_FORCE = 4;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_AUTO = 5;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_INTENSITY = 6;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const MODE_UNDEFINED = 7;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SOURCE_FREQUENCY_32000_OR_LOWER = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SOURCE_FREQUENCY_44100 = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SOURCE_FREQUENCY_48000 = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SOURCE_FREQUENCY_HIGHER = 3;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SURROUND_NONE = 0;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SURROUND_DPL = 1;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SURROUND_DPL2 = 2;
    /**
     * Vollbehr Media Library
     * @var integer
     */
    public const SURROUND_AMBISONIC = 3;
    /** @var string */
    private $_version;

    private readonly float | int $_revision;

    private readonly float | int $_vbrMethod;

    private readonly int | float $_lowpass;

    /** @var integer */
    private $_peakSignalAmplitude;

    /** @var integer */
    private readonly array $_radioReplayGain;

    /** @var integer */
    private readonly array $_audiophileReplayGain;

    private readonly float | int $_encodingFlags;

    private readonly float | int $_athType;

    /** @var integer */
    private $_bitrate;

    private readonly float | int $_encoderDelaySamples;

    private readonly float | int $_paddedSamples;

    private readonly float | int $_sourceSampleFrequency;

    private readonly bool $_unwiseSettingsUsed;

    private readonly float | int $_mode;

    private readonly float | int $_noiseShaping;

    private readonly float | int $_mp3Gain;

    private readonly float | int $_surroundInfo;

    private readonly float | int $_presetUsed;

    /** @var integer */
    private $_musicLength;

    /** @var integer */
    private $_musicCrc;

    /** @var integer */
    private $_crc;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the bitstream.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array          $options Array of options.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_version = $this->_reader->readString8(5);

        $tmp              = $this->_reader->readUInt8();
        $this->_revision  = \Vollbehr\Bit\Twiddling::getValue($tmp, 4, 8);
        $this->_vbrMethod = \Vollbehr\Bit\Twiddling::getValue($tmp, 0, 3);

        $this->_lowpass = $this->_reader->readUInt8() * 100;

        $this->_peakSignalAmplitude = $this->_reader->readUInt32BE();

        $tmp                    = $this->_reader->readUInt16BE();
        $this->_radioReplayGain = [
            'name' => \Vollbehr\Bit\Twiddling::getValue($tmp, 0, 2),
            'originator' => \Vollbehr\Bit\Twiddling::getValue($tmp, 3, 5),
            'absoluteGainAdjustment' =>
                \Vollbehr\Bit\Twiddling::getValue($tmp, 7, 15) / 10,
        ];

        $tmp                         = $this->_reader->readUInt16BE();
        $this->_audiophileReplayGain = [
            'name' => \Vollbehr\Bit\Twiddling::getValue($tmp, 0, 2),
            'originator' => \Vollbehr\Bit\Twiddling::getValue($tmp, 3, 5),
            'absoluteGainAdjustment' =>
                \Vollbehr\Bit\Twiddling::getValue($tmp, 7, 15) / 10,
        ];

        $tmp                  = $this->_reader->readUInt8();
        $this->_encodingFlags = \Vollbehr\Bit\Twiddling::getValue($tmp, 4, 8);
        $this->_athType       = \Vollbehr\Bit\Twiddling::getValue($tmp, 0, 3);

        $this->_bitrate = $this->_reader->readUInt8();

        $tmp = $this->_reader->readUInt32BE();
        // Encoder delay fields
        $this->_encoderDelaySamples = \Vollbehr\Bit\Twiddling::getValue($tmp, 20, 31);
        $this->_paddedSamples       = \Vollbehr\Bit\Twiddling::getValue($tmp, 8, 19);
        // Misc field
        $this->_sourceSampleFrequency = \Vollbehr\Bit\Twiddling::getValue($tmp, 6, 7);
        $this->_unwiseSettingsUsed    = \Vollbehr\Bit\Twiddling::testBit($tmp, 5);
        $this->_mode                  = \Vollbehr\Bit\Twiddling::getValue($tmp, 2, 4);
        $this->_noiseShaping          = \Vollbehr\Bit\Twiddling::getValue($tmp, 0, 1);

        $this->_mp3Gain = 2 ** ($this->_reader->readInt8() / 4);

        $tmp                 = $this->_reader->readUInt16BE();
        $this->_surroundInfo = \Vollbehr\Bit\Twiddling::getValue($tmp, 11, 14);
        $this->_presetUsed   = \Vollbehr\Bit\Twiddling::getValue($tmp, 0, 10);

        $this->_musicLength = $this->_reader->readUInt32BE();

        $this->_musicCrc = $this->_reader->readUInt16BE();
        $this->_crc      = $this->_reader->readUInt16BE();
    }

    /**
     * Returns the version string of the header.
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Returns the info tag revision.
     * @return integer
     */
    public function getRevision(): float | int
    {
        return $this->_revision;
    }

    /**
     * Returns the VBR method used for encoding. See the corresponding constants
     * for possible return values.
     * @return integer
     */
    public function getVbrMethod(): float | int
    {
        return $this->_vbrMethod;
    }

    /**
     * Returns the lowpass filter value.
     * @return integer
     */
    public function getLowpass(): int | float
    {
        return $this->_lowpass;
    }

    /**
     * Returns the peak signal amplitude field of replay gain. The value of 1.0
     * (ie 100%) represents maximal signal amplitude storeable in decoding
     * format.
     * @return integer
     */
    public function getPeakSignalAmplitude()
    {
        return $this->_peakSignalAmplitude;
    }

    /**
     * Returns the radio replay gain field of replay gain, required to make all
     * tracks equal loudness, as an array that consists of the following keys.
     *   o name -- Specifies the name of the gain adjustment. Can be one of the
     *     following values: 0 = not set, 1 = radio, or 2 = audiophile.
     *   o originator -- Specifies the originator of the gain adjustment. Can be
     *     one of the following values: 0 = not set, 1 = set by artist, 2 = set
     *     by user, 3 = set by my model, 4 = set by simple RMS average.
     *   o absoluteGainAdjustment -- Speficies the absolute gain adjustment.
     */
    public function getRadioReplayGain(): array
    {
        return $this->_radioReplayGain;
    }

    /**
     * Returns the audiophile replay gain field of replay gain, required to give
     * ideal listening loudness, as an array that consists of the following
     * keys.
     *   o name -- Specifies the name of the gain adjustment. Can be one of the
     *     following values: 0 = not set, 1 = radio, or 2 = audiophile.
     *   o originator -- Specifies the originator of the gain adjustment. Can be
     *     one of the following values: 0 = not set, 1 = set by artist, 2 = set
     *     by user, 3 = set by my model, 4 = set by simple RMS average.
     *   o absoluteGainAdjustment -- Speficies the absolute gain adjustment.
     */
    public function getAudiophileReplayGain(): array
    {
        return $this->_audiophileReplayGain;
    }

    /**
     * Returns the encoding flags. See the corresponding flag constants for
     * possible values.
     * @return integer
     */
    public function getEncodingFlags(): float | int
    {
        return $this->_encodingFlags;
    }

    /**
     * Returns the ATH type.
     * @return integer
     */
    public function getAthType(): float | int
    {
        return $this->_athType;
    }

    /**
     * Returns the bitrate for CBR encoded files and the minimal birate for
     * VBR encoded file. The maximum value of this field is 255 even with higher
     * actual bitrates.
     * @return integer
     */
    public function getBitrate()
    {
        return $this->_bitrate;
    }

    /**
     * Returns the encoder delay or number of samples added at start.
     * @return integer
     */
    public function getEncoderDelaySamples(): float | int
    {
        return $this->_encoderDelaySamples;
    }

    /**
     * Returns the number of padded samples to complete the last frame.
     * @return integer
     */
    public function getPaddedSamples(): float | int
    {
        return $this->_paddedSamples;
    }

    /**
     * Returns the source sample frequency. See corresponding constants for
     * possible values.
     * @return integer
     */
    public function getSourceSampleFrequency(): float | int
    {
        return $this->_sourceSampleFrequency;
    }

    /**
     * An alias to getUnwiseSettingsUsed().
     * @see getUnwiseSettingsUsed
     * @return boolean
     */
    public function areUnwiseSettingsUsed()
    {
        return $this->getUnwiseSettingsUsed();
    }

    /**
     * Returns whether unwise settings were used to encode the file.
     */
    public function getUnwiseSettingsUsed(): bool
    {
        return $this->_unwiseSettingsUsed;
    }

    /**
     * Returns the stereo mode. See corresponding constants for possible values.
     * @return integer
     */
    public function getMode(): float | int
    {
        return $this->_mode;
    }

    /**
     * Returns the noise shaping.
     * @return integer
     */
    public function getNoiseShaping(): float | int
    {
        return $this->_noiseShaping;
    }

    /**
     * Returns the MP3 gain change. Any MP3 can be amplified in a lossless
     * manner. If done so, this field can be used to log such transformation
     * happened so that any given time it can be undone.
     * @return integer
     */
    public function getMp3Gain(): float | int
    {
        return $this->_mp3Gain;
    }

    /**
     * Returns the surround info. See corresponding contants for possible
     * values.
     * @return integer
     */
    public function getSurroundInfo(): float | int
    {
        return $this->_surroundInfo;
    }

    /**
     * Returns the preset used in encoding.
     * @return integer
     */
    public function getPresetUsed(): float | int
    {
        return $this->_presetUsed;
    }

    /**
     * Returns the exact length in bytes of the MP3 file originally made by LAME
     * excluded ID3 tag info at the end.
     * The first byte it counts is the first byte of this LAME header and the
     * last byte it counts is the last byte of the last MP3 frame containing
     * music. The value should be equal to file length at the time of LAME
     * encoding, except when using ID3 tags.
     * @return integer
     */
    public function getMusicLength()
    {
        return $this->_musicLength;
    }

    /**
     * Returns the CRC-16 of the complete MP3 music data as made originally by
     * LAME.
     * @return integer
     */
    public function getMusicCrc()
    {
        return $this->_musicCrc;
    }

    /**
     * Returns the CRC-16 of the first 190 bytes of the header frame.
     * @return integer
     */
    public function getCrc()
    {
        return $this->_crc;
    }
}
