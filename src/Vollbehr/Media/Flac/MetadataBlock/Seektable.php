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
 * This class represents the seektable metadata block. This is an optional block for storing seek points. It is possible
 * to seek to any given sample in a FLAC stream without a seek table, but the delay can be unpredictable since the
 * bitrate may vary widely within a stream. By adding seek points to a stream, this delay can be significantly reduced.
 * Each seek point takes 18 bytes, so 1% resolution within a stream adds less than 2k. There can be only one SEEKTABLE
 * in a stream, but the table can have any number of seek points. There is also a special 'placeholder' seekpoint which
 * will be ignored by decoders but which can be used to reserve space for future seek point insertion.
 * @author Sven Vollbehr
 */
final class Seektable extends \Vollbehr\Media\Flac\MetadataBlock
{
    private array $_seekpoints = [];

    /**
     * Constructs the class with given parameters and parses object related data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $seekpointCount = $this->getSize() / 18;
        for ($i = 0; $i < $seekpointCount; $i++) {
            $this->_seekpoints[] = [
                'sampleNumber' => $this->_reader->readInt64BE(),
                'offset' => $this->_reader->readInt64BE(),
                'numberOfSamples' => $this->_reader->readUInt16BE(),
            ];
        }
    }

    /**
     * Returns the seekpoint table. The array consists of items having three keys.
     *   o sampleNumber    --  Sample number of first sample in the target frame, or 0xFFFFFFFFFFFFFFFF for a
     *                         placeholder point.
     *   o offset          --  Offset (in bytes) from the first byte of the first frame header to the first byte of the
     *                         target frame's header.
     *   o numberOfSamples --  Number of samples in the target frame.
     */
    public function getSeekpoints(): array
    {
        return $this->_seekpoints;
    }
}
