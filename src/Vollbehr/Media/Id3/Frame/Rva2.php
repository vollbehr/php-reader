<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Relative volume adjustment (2)_ frame is a more subjective frame
 * than the previous ones. It allows the user to say how much he wants to
 * increase/decrease the volume on each channel when the file is played. The
 * purpose is to be able to align all files to a reference volume, so that you
 * don't have to change the volume constantly. This frame may also be used to
 * balance adjust the audio.
 * The volume adjustment is encoded in a way giving the scale of +/- 64 dB with
 * a precision of 0.001953125 dB.
 * There may be more than one RVA2 frame in each tag, but only one with the same
 * identification string.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 * @since      ID3v2.4.0
 */
final class Rva2 extends \Vollbehr\Media\Id3\Frame
{
    /**
     * The channel type key.
     * @see $types
     * @var string
     */
    public const channelType = 'channelType';
    /**
     * The volume adjustment key. Adjustments are +/- 64 dB with a precision of
     * 0.001953125 dB.
     * @var string
     */
    public const volumeAdjustment = 'volumeAdjustment';
    /**
     * The peak volume key.
    *
     * @var string
     */
    public const peakVolume = 'peakVolume';
    /**
     * The list of channel types.
    *
     * @var Array
     */
    public static $types = ['Other', 'Master volume', 'Front right', 'Front left', 'Back right',
         'Back left', 'Front centre', 'Back centre', 'Subwoofer'];
    /** @var string */
    private $_device;

    /** @var Array */
    private $_adjustments;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($this->_reader === null) {
            return;
        }

        [$this->_device] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
        $this->_reader->setOffset(strlen((string) $this->_device) + 1);

        for ($i = $j = 0; $i < 9; $i++) {
            $this->_adjustments[$i] = [self::channelType => $this->_reader->readInt8(),
                 self::volumeAdjustment =>
                     $this->_reader->readInt16BE() / 512.0];
            $bitsInPeak  = $this->_reader->readInt8();
            $bytesInPeak = $bitsInPeak > 0 ? ceil($bitsInPeak / 8) : 0;
            switch ($bytesInPeak) {
                case 8:
                    $this->_adjustments[$i][self::peakVolume] = $this->_reader->readInt64BE();
                    break;
                case 4:
                    $this->_adjustments[$i][self::peakVolume] = $this->_reader->readUInt32BE();
                    break;
                case 2:
                    $this->_adjustments[$i][self::peakVolume] = $this->_reader->readUInt16BE();
                    break;
                case 1:
                    $this->_adjustments[$i][self::peakVolume] = $this->_reader->readUInt8();
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Returns the device where the adjustments should apply.
     * @return string
     */
    public function getDevice()
    {
        return $this->_device;
    }
    /**
     * Sets the device where the adjustments should apply.
     * @param string $device The device.
     */
    public function setDevice($device): void
    {
        $this->_device = $device;
    }
    /**
     * Returns the array containing volume adjustments for each channel. Volume
     * adjustments are arrays themselves containing the following keys:
     * channelType, volumeAdjustment, peakVolume.
     * @return Array
     */
    public function getAdjustments()
    {
        return $this->_adjustments;
    }
    /**
     * Sets the array of volume adjustments for each channel. Each volume
     * adjustment is an array too containing the following keys: channelType,
     * volumeAdjustment, peakVolume.
     * @param Array $adjustments The volume adjustments array.
     */
    public function setAdjustments($adjustments): void
    {
        $this->_adjustments = $adjustments;
    }
    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8($this->_device, 1);
        foreach ($this->_adjustments as $channel) {
            $writer->writeInt8($channel[self::channelType])
                   ->writeInt16BE($channel[self::volumeAdjustment] * 512);
            if (abs($channel[self::peakVolume]) <= 0xff) {
                $writer->writeInt8(8)
                       ->writeUInt8($channel[self::peakVolume]);
            } elseif (abs($channel[self::peakVolume]) <= 0xffff) {
                $writer->writeInt8(16)
                       ->writeUInt16BE($channel[self::peakVolume]);
            } elseif (abs($channel[self::peakVolume]) <= 0xffffffff) {
                $writer->writeInt8(32)
                       ->writeUInt32BE($channel[self::peakVolume]);
            } else {
                $writer->writeInt8(64)
                       ->writeInt64BE($channel[self::peakVolume]); // UInt64
            }
        }
    }
}
