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
 * This class represents a file containing Vorbis bitstream as described in
 * {@see http://xiph.org/vorbis/doc/Vorbis_I_spec.pdf Vorbis I specification}.
 * Vorbis is a general purpose perceptual audio CODEC intended to allow maximum encoder exibility, thus allowing it to
 * scale competitively over an exceptionally wide range of bitrates. At the high quality/bitrate end of the scale (CD
 * or DAT rate stereo, 16/24 bits) it is in the same league as MPEG-2 and MPC. Similarly, the 1.0 encoder can encode
 * high-quality CD and DAT rate stereo at below 48kbps without resampling to a lower rate. Vorbis is also intended for
 * lower and higher sample rates (from 8kHz telephony to 192kHz digital masters) and a range of channel representations
 * (monaural, polyphonic, stereo, quadraphonic, 5.1, ambisonic, or up to 255 discrete channels).
 * @author Sven Vollbehr
 * @todo       Setup header is not yet supported
 */
final class Vorbis
{
    private ?\Vollbehr\Io\FileReader $_reader;

    private readonly Vorbis\Header\Identification $_identificationHeader;

    private readonly Vorbis\Header\Comment $_commentHeader;

    private readonly Vorbis\Header\Setup $_setupHeader;

    /**
     * Constructs the \Vollbehr\Media\Vorbis class with given file.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file,
     *  file descriptor of an opened file, or a {@see \Vollbehr\Io\Reader} instance.
     * @throws \Vollbehr\Io\Exception if an error occur in stream handling.
     * @throws Vorbis\Exception if an error occurs in vorbis bitstream reading.
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

                throw new Vorbis\Exception($e->getMessage());
            }
        }

        $this->_identificationHeader = new Vorbis\Header\Identification($this->_reader);
        $this->_commentHeader        = new Vorbis\Header\Comment($this->_reader);
        $this->_setupHeader          = new Vorbis\Header\Setup($this->_reader);
    }

    /**
     * Returns the identification header.
     */
    public function getIdentificationHeader(): Vorbis\Header\Identification
    {
        return $this->_identificationHeader;
    }

    /**
     * Returns the comment header.
     */
    public function getCommentHeader(): Vorbis\Header\Comment
    {
        return $this->_commentHeader;
    }

    /**
     * Returns the setup header.
     */
    public function getSetupHeader(): Vorbis\Header\Setup
    {
        return $this->_setupHeader;
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The field name.
     */
    public function __get(string $name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        } else {

            throw new Vorbis\Exception('Unknown field: ' . $name);
        }
    }
}
