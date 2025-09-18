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
 * The _Relative volume adjustment_ frame is a more subjective function
 * than the previous ones. It allows the user to say how much he wants to
 * increase/decrease the volume on each channel while the file is played. The
 * purpose is to be able to align all files to a reference volume, so that you
 * don't have to change the volume constantly. This frame may also be used to
 * balance adjust the audio.
 * There may only be one RVAD frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 * @deprecated ID3v2.3.0
 */
final class Rvad extends \Vollbehr\Media\Id3\Frame
{
    /* The required keys. */

    /**
     * Vollbehr Media Library
     * @var string
     */
    public const right = 'right';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const left = 'left';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const peakRight = 'peakRight';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const peakLeft = 'peakLeft';
    /* The optional keys. */
    /**
     * Vollbehr Media Library
     * @var string
     */
    public const rightBack = 'rightBack';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const leftBack = 'leftBack';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const peakRightBack = 'peakRightBack';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const peakLeftBack = 'peakLeftBack';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const center = 'center';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const peakCenter = 'peakCenter';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const bass = 'bass';
    /**
     * Vollbehr Media Library
    *
     * @var string
     */
    public const peakBass = 'peakBass';
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

        $flags           = $this->_reader->readInt8();
        $descriptionBits = $this->_reader->readInt8();
        if ($descriptionBits <= 8 || $descriptionBits > 16) {

            throw new \Vollbehr\Media\Id3\Exception('Unsupported description bit size of: ' . $descriptionBits);
        }

        $this->_adjustments[self::right] = ($flags & 0x1) == 0x1 ?
             $this->_reader->readUInt16BE() : -$this->_reader->readUInt16BE();
        $this->_adjustments[self::left] = ($flags & 0x2) == 0x2 ?
             $this->_reader->readUInt16BE() : -$this->_reader->readUInt16BE();
        $this->_adjustments[self::peakRight] = $this->_reader->readUInt16BE();
        $this->_adjustments[self::peakLeft]  = $this->_reader->readUInt16BE();

        if (!$this->_reader->available()) {
            return;
        }

        $this->_adjustments[self::rightBack] = ($flags & 0x4) == 0x4 ?
             $this->_reader->readUInt16BE() : -$this->_reader->readUInt16BE();
        $this->_adjustments[self::leftBack] = ($flags & 0x8) == 0x8 ?
             $this->_reader->readUInt16BE() : -$this->_reader->readUInt16BE();
        $this->_adjustments[self::peakRightBack] = $this->_reader->readUInt16BE();
        $this->_adjustments[self::peakLeftBack]  = $this->_reader->readUInt16BE();

        if (!$this->_reader->available()) {
            return;
        }

        $this->_adjustments[self::center] = ($flags & 0x10) == 0x10 ?
             $this->_reader->readUInt16BE() : -$this->_reader->readUInt16BE();
        $this->_adjustments[self::peakCenter] = $this->_reader->readUInt16BE();

        if (!$this->_reader->available()) {
            return;
        }

        $this->_adjustments[self::bass] = ($flags & 0x20) == 0x20 ?
             $this->_reader->readUInt16BE() : -$this->_reader->readUInt16BE();
        $this->_adjustments[self::peakBass] = $this->_reader->readUInt16BE();
    }

    /**
     * Returns the array containing the volume adjustments. The array must
     * contain the following keys: right, left, peakRight, peakLeft. It may
     * optionally contain the following keys: rightBack, leftBack,
     * peakRightBack, peakLeftBack, center, peakCenter, bass, and peakBass.
     * @return Array
     */
    public function getAdjustments()
    {
        return $this->_adjustments;
    }
    /**
     * Sets the array of volume adjustments. The array must contain the
     * following keys: right, left, peakRight, peakLeft. It may optionally
     * contain the following keys: rightBack, leftBack, peakRightBack,
     * peakLeftBack, center, peakCenter, bass, and peakBass.
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
        $writer->writeInt8($flags = 0);
        if ($this->_adjustments[self::right] > 0) {
            $flags |= 0x1;
        }
        if ($this->_adjustments[self::left] > 0) {
            $flags |= 0x2;
        }
        $writer->writeInt8(16)
               ->writeUInt16BE(abs($this->_adjustments[self::right]))
               ->writeUInt16BE(abs($this->_adjustments[self::left]))
               ->writeUInt16BE(abs($this->_adjustments[self::peakRight]))
               ->writeUInt16BE(abs($this->_adjustments[self::peakLeft]));

        if (isset($this->_adjustments[self::rightBack]) &&
            isset($this->_adjustments[self::leftBack]) &&
            isset($this->_adjustments[self::peakRightBack]) &&
            isset($this->_adjustments[self::peakLeftBack])) {
            if ($this->_adjustments[self::rightBack] > 0) {
                $flags |= 0x4;
            }
            if ($this->_adjustments[self::leftBack] > 0) {
                $flags |= 0x8;
            }
            $writer->writeUInt16BE(abs($this->_adjustments[self::rightBack]))
                   ->writeUInt16BE(abs($this->_adjustments[self::leftBack]))
                   ->writeUInt16BE(abs($this->_adjustments[self::peakRightBack]))
                   ->writeUInt16BE(abs($this->_adjustments[self::peakLeftBack]));
        }

        if (isset($this->_adjustments[self::center]) &&
            isset($this->_adjustments[self::peakCenter])) {
            if ($this->_adjustments[self::center] > 0) {
                $flags |= 0x10;
            }
            $writer->writeUInt16BE(abs($this->_adjustments[self::center]))
                   ->writeUInt16BE(abs($this->_adjustments[self::peakCenter]));
        }

        if (isset($this->_adjustments[self::bass]) &&
                isset($this->_adjustments[self::peakBass])) {
            if ($this->_adjustments[self::bass] > 0) {
                $flags |= 0x20;
            }
            $writer->writeUInt16BE(abs($this->_adjustments[self::bass]))
                   ->writeUInt16BE(abs($this->_adjustments[self::peakBass]));
        }
        $writer->setOffset(0);
        $writer->writeInt8($flags);
    }
}
