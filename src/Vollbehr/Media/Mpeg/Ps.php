<?php

declare(strict_types=1);

namespace Vollbehr\Media\Mpeg;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * This class represents a MPEG Program Stream encoded file as described in
 * MPEG-1 Systems (ISO/IEC 11172-1) and MPEG-2 Systems (ISO/IEC 13818-1)
 * standards.
 * The Program Stream is a stream definition which is tailored for communicating
 * or storing one program of coded data and other data in environments where
 * errors are very unlikely, and where processing of system coding, e.g. by
 * software, is a major consideration.
 * This class only supports the parsing of the play duration.
 * @author Sven Vollbehr
 * @todo       Full implementation
 */
final class Ps extends BaseObject
{
    private readonly float | int $_length;

    /**
     * Constructs the class with given file and options.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file,
     *  file descriptor of an opened file, or a {@see \Vollbehr\Io\Reader} instance.
     * @param Array                          $options  The options array.
     */
    public function __construct($filename, $options = [])
    {
        if ($filename instanceof \Vollbehr\Io\Reader) {
            $this->_reader = &$filename;
        } else {

            try {
                $this->_reader = new \Vollbehr\Io\FileReader($filename);
            } catch (\Vollbehr\Io\Exception $e) {
                $this->_reader = null;

                throw new Exception($e->getMessage());
            }
        }
        $this->setOptions($options);

        $startCode    = 0;
        $startTime    = 0;
        $pictureCount = 0;
        $pictureRate  = 0;
        $rates        = [ 0, 23.976, 24, 25, 29.97, 30, 50, 59.94, 60 ];
        $foundSeqHdr  = false;
        $foundGOP     = false;

        do {
            do {
                $startCode = $this->nextStartCode();
            } while ($startCode != 0x1b3 && $startCode != 0x1b8);

            if ($startCode == 0x1b3 /* sequence_header_code */ &&
                    $pictureRate == 0) {
                $i1 = $this->_reader->readUInt32BE();
                $i2 = $this->_reader->readUInt32BE();
                if (!\Vollbehr\Bit\Twiddling::testAllBits($i2, 0x2000)) {

                    throw new Exception('File does not contain a valid MPEG Program Stream (Invalid mark)');
                }
                $pictureRate = $rates[\Vollbehr\Bit\Twiddling::getValue($i1, 4, 8)];
                $foundSeqHdr = true;
            }
            if ($startCode == 0x1b8 /* group_start_code */) {
                $tmp       = $this->_reader->readUInt32BE();
                $startTime = (($tmp >> 26) & 0x1f) * 60 * 60 * 1000 /* hours */ +
                    (($tmp >> 20) & 0x3f) * 60 * 1000 /* minutes */ +
                    (($tmp >> 13) & 0x3f) * 1000 /* seconds */ +
                    (int)(1 / $pictureRate * (($tmp >> 7) & 0x3f) * 1000);
                $foundGOP = true;
            }
        } while (!$foundSeqHdr || !$foundGOP);

        $this->_reader->setOffset($this->_reader->getSize());

        do {
            if (($startCode = $this->prevStartCode()) == 0x100) {
                $pictureCount++;
            }
        } while ($startCode != 0x1b8);

        $this->_reader->skip(4);
        $tmp           = $this->_reader->readUInt32BE();
        $this->_length = (((($tmp >> 26) & 0x1f) * 60 * 60 * 1000 /* hours */ +
              (($tmp >> 20) & 0x3f) * 60 * 1000 /* minutes */ +
              (($tmp >> 13) & 0x3f) * 1000 /* seconds */ +
             (int)(1 / $pictureRate * (($tmp >> 7) & 0x3f) * 1000)) -
                 $startTime +
             (int)(1 / $pictureRate * $pictureCount * 1000)) / 1000;
    }

    /**
     * Returns the exact playtime in seconds.
     * @return integer
     */
    public function getLength(): int | float
    {
        return $this->_length;
    }

    /**
     * Returns the exact playtime given in seconds as a string in the form of
     * [hours:]minutes:seconds.milliseconds.
     * @return string
     */
    public function getFormattedLength()
    {
        return $this->formatTime($this->getLength());
    }
}
