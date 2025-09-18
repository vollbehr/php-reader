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
 * This class represents a file in Resource Interchange File Format as described in Multimedia Programming Interface
 * and Data Specifications 1.0 by Microsoft Corporation (April 15, 1994; Revision: 3.0).
 * The Resource Interchange File Format (RIFF), a tagged file structure, is a general specification upon which many file
 * formats can be defined. The main advantage of RIFF is its extensibility; file formats based on RIFF can be
 * future-proofed, as format changes can be ignored by existing applications. The RIFF file format is suitable for the
 * following multimedia tasks:
 *  o Playing back multimedia data
 *  o Recording multimedia data
 *  o Exchanging multimedia data between applications and across platforms
 * The structure of a RIFF file is similar to the structure of an Electronic Arts IFF file. RIFF is not actually a file
 * format itself (since it does not represent a specific kind of information), but its name contains the words
 * interchange file format in recognition of its roots in IFF. Refer to the EA IFF definition document, EA IFF 85
 * Standard for Interchange Format Files, for a list of reasons to use a tagged file format. The following is current
 * (as per revision 3.0 of the specification) list of registered RIFF types.
 *  o PAL  -- RIFF Palette Format
 *  o RDIB -- RIFF Device Independent Bitmap Format
 *  o RMID -- RIFF MIDI Format
 *  o RMMP -- RIFF Multimedia Movie File Format
 *  o WAVE -- Waveform Audio Format
 * @author Sven Vollbehr
 */
final class Riff extends Riff\ContainerChunk
{
    /**
     * Constructs the class with given file.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file, file descriptor of an opened file, or a
     *  {@see \Vollbehr\Io\Reader} instance.
     * @throws Riff\Exception if given file descriptor is not valid or an error occurs in stream handling.
     */
    public function __construct($filename)
    {
        if ($filename instanceof \Vollbehr\Io\Reader) {
            $reader = &$filename;
        } else {
            try {
                $reader = new \Vollbehr\Io\FileReader($filename);
            } catch (\Vollbehr\Io\Exception $e) {

                throw new Riff\Exception($e->getMessage());
            }
        }

        parent::__construct($reader);
    }
}
