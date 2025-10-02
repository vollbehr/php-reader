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
 * This class represents an MPEG Audio Bit Stream as described in
 * ISO/IEC 11172-3 and ISO/IEC 13818-3 standards.
 * Non-standard VBR header extensions or namely XING, VBRI and LAME headers are
 * supported.
 * This class is optimized for fast determination of the play duration of the
 * file and hence uses lazy data reading mode by default. In this mode the
 * actual frames and frame data are only read when referenced directly. You may
 * change this behaviour by giving an appropriate option to the constructor.
 * @author Ryan Butterfield
 * @author Sven Vollbehr
 * @todo       Implement validation routines
 */
final class Abs extends Abs\BaseObject
{
    private int $_bytes;

    private array $_frames = [];

    private ?Abs\XingHeader $_xingHeader = null;

    private ?Abs\LameHeader $_lameHeader = null;

    private ?Abs\VbriHeader $_vbriHeader = null;

    private int $_cumulativeBitrate = 0;

    private float $_cumulativePlayDuration = 0.0;

    private float $_estimatedBitrate = 0.0;

    private float $_estimatedPlayDuration = 0.0;

    private int|false $_lastFrameOffset = false;

    /**
     * Constructs the \Vollbehr\Media\Mpeg\ABS class with given file and options.
     * The following options are currently recognized:
     *   o readmode -- Can be one of full or lazy and determines when the read
     *     of frames and their data happens. When in full mode the data is read
     *     automatically during the instantiation of the frame and all the
     *     frames are read during the instantiation of this class. While this
     *     allows faster validation and data fetching, it is unnecessary in
     *     terms of determining just the play duration of the file. Defaults to
     *     lazy.
     *   o estimatePrecision -- Only applicaple with lazy read mode to determine
     *     the precision of play duration estimate. This precision is equal to
     *     how many frames are read before fixing the average bitrate that is
     *     used to calculate the play duration estimate of the whole file. Each
     *     frame adds about 0.1-0.2ms to the processing of the file. Defaults to
     *     1000.
     * When in lazy data reading mode it is first checked whether a VBR header
     * is found in a file. If so, the play duration is calculated based no its
     * data and no further frames are read from the file. If no VBR header is
     * found, frames up to estimatePrecision are read to calculate an average
     * bitrate.
     * Hence, only zero or <var>estimatePrecision</var> number of frames are
     * read in lazy data reading mode. The rest of the frames are read
     * automatically when directly referenced, ie the data is read when it is
     * needed.
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

        $offset       = $this->_reader->getOffset();
        $this->_bytes = $this->_reader->getSize();

        /* Skip ID3v1 tag */
        $this->_reader->setOffset(-128);
        if ($this->_reader->read(3) == 'TAG') {
            $this->_bytes -= 128;
        }
        $this->_reader->setOffset($offset);

        /* Skip ID3v2 tags (some files errorneusly contain multiple tags) */
        while ($this->_reader->readString8(3) == 'ID3') {

            $header = new \Vollbehr\Media\Id3\Header($this->_reader);
            $this->_reader->skip($header->getSize() +
                 ($header->hasFlag(\Vollbehr\Media\Id3\Header::FOOTER) ? 10 : 0));
            $offset = $this->_reader->getOffset();
        }
        $this->_reader->setOffset($offset);

        /* Check whether the ABS is contained within a RIFF chunk */
        $offset = $this->_reader->getOffset();

        if ($this->_reader->readString8(4) == 'RIFF') {
            $riffSize = $this->_reader->readUInt32LE();
            $riffType = $this->_reader->read(4); // WAVE

            while ($this->_reader->getOffset() < $offset + 8 + $riffSize - 1) {
                $chunkId   = $this->_reader->read(4);
                $chunkSize = $this->_reader->readUInt32LE();

                if ($chunkId == 'fmt ') {
                    if ($this->_reader->readInt16LE() != 85) { // 85: MPEG-1 Layer 3 Codec

                        throw new Exception('File does not contain a valid MPEG Audio Bit Stream (Contains RIFF with no MPEG ABS)');
                    } else {
                        $this->_reader->skip($chunkSize - 2);
                    }
                } elseif ($chunkId == 'data') {
                    $offset = $this->_reader->getOffset();
                    break;
                } else {
                    $this->_reader->skip($chunkSize);
                }
            }
        } else {
            $this->_reader->setOffset($offset);
        }

        /* Check for VBR headers */
        $this->_frames[] = $firstFrame = new Abs\Frame($this->_reader, $options);

        $offset = $this->_reader->getOffset();

        $this->_reader->setOffset($firstFrame->getOffset() + 4 + self::$sidesizes
             [$firstFrame->getFrequencyType()][$firstFrame->getMode()]);
        if (($xing = $this->_reader->readString8(4)) == 'Xing' ||
                $xing == 'Info') {

            $this->_xingHeader = new Abs\XingHeader($this->_reader, $options);
            if ($this->_reader->readString8(4) == 'LAME') {

                $this->_lameHeader = new Abs\LameHeader($this->_reader, $options);
            }

            // A header frame is not counted as an audio frame
            array_pop($this->_frames);
        }

        $this->_reader->setOffset($firstFrame->getOffset() + 4 + 32);
        if ($this->_reader->readString8(4) == 'VBRI') {

            $this->_vbriHeader = new Abs\VbriHeader($this->_reader, $options);

            // A header frame is not counted as an audio frame
            array_pop($this->_frames);
        }

        $this->_reader->setOffset($offset);

        // Ensure we always have read at least one frame
        if ($this->_frames === []) {
            $this->_readFrames(1);
        }

        /* Read necessary frames */
        if ($this->getOption('readmode', 'lazy') == 'lazy') {
            if ((($header = $this->_xingHeader) instanceof Abs\XingHeader ||
                 ($header = $this->_vbriHeader) instanceof Abs\VbriHeader) &&
                 $header->getFrames() != 0) {
                $this->_estimatedPlayDuration = $header->getFrames() *
                    $firstFrame->getSamples() /
                    $firstFrame->getSamplingFrequency();
                if ($this->_lameHeader instanceof Abs\LameHeader) {
                    $this->_estimatedBitrate = $this->_lameHeader->getBitrate();
                    if ($this->_estimatedBitrate == 255) {
                        $this->_estimatedBitrate = round(($this->_lameHeader->getMusicLength()) /
                             (($header->getFrames() *
                               $firstFrame->getSamples()) /
                              $firstFrame->getSamplingFrequency()) / 1000 * 8);
                    }
                } else {
                    $this->_estimatedBitrate = ($this->_bytes - $firstFrame->getOffset()) /
                        $this->_estimatedPlayDuration / 1000 * 8;
                }
            } else {
                $this->_readFrames($this->getOption('estimatePrecision', 1000));

                $this->_estimatedBitrate      = $this->_cumulativeBitrate / count($this->_frames);
                $this->_estimatedPlayDuration = ($this->_bytes - $firstFrame->getOffset()) /
                    ($this->_estimatedBitrate * 1000 / 8);
            }
        } else {
            $this->_readFrames();

            $this->_estimatedBitrate      = $this->_cumulativeBitrate / count($this->_frames);
            $this->_estimatedPlayDuration = $this->_cumulativePlayDuration;
        }
    }

    /**
     * Returns <var>true</var> if the audio bitstream contains the Xing VBR
     * header, or <var>false</var> otherwise.
     */
    public function hasXingHeader(): bool
    {
        return $this->_xingHeader instanceof Abs\XingHeader;
    }

    /**
     * Returns the Xing VBR header, or <var>null</var> if not found in the audio
     * bitstream.
     */
    public function getXingHeader(): ?Abs\XingHeader
    {
        return $this->_xingHeader;
    }

    /**
     * Returns <var>true</var> if the audio bitstream contains the LAME VBR
     * header, or <var>false</var> otherwise.
     */
    public function hasLameHeader(): bool
    {
        return $this->_lameHeader instanceof Abs\LameHeader;
    }

    /**
     * Returns the LAME VBR header, or <var>null</var> if not found in the audio
     * bitstream.
     */
    public function getLameHeader(): ?Abs\LameHeader
    {
        return $this->_lameHeader;
    }

    /**
     * Returns <var>true</var> if the audio bitstream contains the Fraunhofer
     * IIS VBR header, or <var>false</var> otherwise.
     */
    public function hasVbriHeader(): bool
    {
        return $this->_vbriHeader instanceof Abs\VbriHeader;
    }

    /**
     * Returns the Fraunhofer IIS VBR header, or <var>null</var> if not found in
     * the audio bitstream.
     */
    public function getVbriHeader(): ?Abs\VbriHeader
    {
        return $this->_vbriHeader;
    }

    /**
     * Returns the bitrate estimate. This value is either fetched from one of
     * the headers or calculated based on the read frames.
     * @return integer
     */
    public function getBitrateEstimate()
    {
        return $this->_estimatedBitrate;
    }

    /**
     * For variable bitrate files this method returns the exact average bitrate
     * of the whole file.
     * @return integer
     */
    public function getBitrate(): int | float
    {
        if ($this->getOption('readmode', 'lazy') == 'lazy') {
            $this->_readFrames();
        }

        return $this->_cumulativeBitrate / count($this->_frames);
    }

    /**
     * Returns the playtime estimate, in seconds.
     * @return integer
     */
    public function getLengthEstimate()
    {
        return $this->_estimatedPlayDuration;
    }

    /**
     * Returns the exact playtime in seconds. In lazy reading mode the frames
     * are read from the file the first time you call this method to get the
     * exact playtime of the file.
     */
    public function getLength(): int
    {
        if ($this->getOption('readmode', 'lazy') == 'lazy') {
            $this->_readFrames();
        }

        return $this->_cumulativePlayDuration;
    }

    /**
     * Returns the playtime estimate as a string in the form of
     * [hours:]minutes:seconds.milliseconds.
     * @return string
     */
    public function getFormattedLengthEstimate()
    {
        return $this->formatTime($this->getLengthEstimate());
    }

    /**
     * Returns the exact playtime given in seconds as a string in the form of
     * [hours:]minutes:seconds.milliseconds. In lazy reading mode the frames are
     * read from the file the first time you call this method to get the exact
     * playtime of the file.
     * @return string
     */
    public function getFormattedLength()
    {
        return $this->formatTime($this->getLength());
    }

    /**
     * Returns all the frames of the audio bitstream as an array. In lazy
     * reading mode the frames are read from the file the first time you call
     * this method.
     */
    public function getFrames(): array
    {
        if ($this->getOption('readmode', 'lazy') == 'lazy') {
            $this->_readFrames();
        }

        return $this->_frames;
    }

    /**
     * Reads frames up to given limit. If called subsequently the method
     * continues after the last frame read in the last call, again to read up
     * to the limit or just the rest of the frames.
     * @param integer $limit The maximum number of frames read from the
     *                       bitstream
     */
    private function _readFrames($limit = null): void
    {
        if ($this->_lastFrameOffset !== false) {
            $this->_reader->setOffset($this->_lastFrameOffset);
        }

        for ($i = 0; ($j = $this->_reader->getOffset()) < $this->_bytes; $i++) {
            $options = $this->getOptions();
            $frame   = new Abs\Frame($this->_reader, $options);

            $this->_cumulativePlayDuration +=
                (float)($frame->getLength() /
                         ($frame->getBitrate() * 1000 / 8));
            $this->_cumulativeBitrate += $frame->getBitrate();
            $this->_frames[] = $frame;

            if ($limit === null) {
                $this->_lastFrameOffset = $this->_reader->getOffset();
            }
            if (($limit !== null && (($i + 1) == $limit)) ||
                ($limit !== null &&
                 ($j + $frame->getLength() >= $this->_bytes))) {
                $this->_lastFrameOffset = $this->_reader->getOffset();
                break;
            }
        }
    }
}
