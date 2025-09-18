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
 * The _Equalisation_ frame is another subjective, alignment frame. It
 * allows the user to predefine an equalisation curve within the audio file.
 * There may only be one EQUA frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 * @deprecated ID3v2.3.0
 */
final class Equa extends \Vollbehr\Media\Id3\Frame
{
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

        $adjustmentBits = $this->_reader->readInt8();
        if ($adjustmentBits <= 8 || $adjustmentBits > 16) {

            throw new \Vollbehr\Media\Id3\Exception('Unsupported adjustment bit size of: ' . $adjustmentBits);
        }

        while ($this->_reader->available()) {
            $frequency                                 = $this->_reader->readUInt16BE();
            $this->_adjustments[($frequency & 0x7fff)] = ($frequency & 0x8000) == 0x8000 ?
                $this->_reader->readUInt16BE() :
                -$this->_reader->readUInt16BE();
        }
        ksort($this->_adjustments);
    }

    /**
     * Returns the array containing adjustments having frequencies as keys and
     * their corresponding adjustments as values.
     * @return Array
     */
    public function getAdjustments()
    {
        return $this->_adjustments;
    }
    /**
     * Adds a volume adjustment setting for given frequency. The frequency can
     * have a value from 0 to 32767 Hz.
     * @param integer $frequency The frequency, in hertz.
     * @param integer $adjustment The adjustment, in dB.
     */
    public function addAdjustment($frequency, $adjustment): void
    {
        $this->_adjustments[$frequency] = $adjustment;
        ksort($this->_adjustments);
    }

    /**
     * Sets the adjustments array. The array must have frequencies as keys and
     * their corresponding adjustments as values. The frequency can have a value
     * from 0 to 32767 Hz. One frequency should only be described once in the
     * frame.
     * @param Array $adjustments The adjustments array.
     */
    public function setAdjustments($adjustments): void
    {
        $this->_adjustments = $adjustments;
        ksort($this->_adjustments);
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeInt8(16);
        foreach ($this->_adjustments as $frequency => $adjustment) {
            $writer->writeUInt16BE($adjustment > 0 ? $frequency | 0x8000 : $frequency & ~0x8000)
                   ->writeUInt16BE(abs($adjustment));
        }
    }
}
