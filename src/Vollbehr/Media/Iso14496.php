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
 * This class represents a file in ISO base media file format as described in
 * ISO/IEC 14496 Part 12 standard.
 * The ISO Base Media File Format is designed to contain timed media information
 * for a presentation in a flexible, extensible format that facilitates
 * interchange, management, editing, and presentation of the media. This
 * presentation may be local to the system containing the presentation, or may
 * be via a network or other stream delivery mechanism.
 * The file structure is object-oriented; a file can be decomposed into
 * constituent objects very simply, and the structure of the objects inferred
 * directly from their type. The file format is designed to be independent of
 * any particular network protocol while enabling efficient support for them in
 * general.
 * The ISO Base Media File Format is a base format for media file formats.
 * An overall view of the normal encapsulation structure is provided in the
 * following table.
 * The table shows those boxes that may occur at the top-level in the left-most
 * column; indentation is used to show possible containment. Thus, for example,
 * a {@see \Vollbehr\Media\Iso14496\Box\Tkhd Track Header Box} is found in a
 * {@see \Vollbehr\Media\Iso14496\Box\Trak Track Box}, which is found in a
 * {@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}. Not all boxes need be used
 * in all files; the mandatory boxes are marked with bold typeface. See the
 * description of the individual boxes for a discussion of what must be assumed
 * if the optional boxes are not present.
 * User data objects shall be placed only in
 * {@see \Vollbehr\Media\Iso14496\Box\Moov Movie} or
 * {@see \Vollbehr\Media\Iso14496\Box\Trak Track Boxes}, and objects using an
 * extended type may be placed in a wide variety of containers, not just the
 * top level.
 * - <b>ftyp</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Ftyp File Type Box}_;
 *     file type and compatibility
 * - pdin -- _{@see \Vollbehr\Media\Iso14496\Box\Pdin Progressive Download
 *     Information Box}_
 * - <b>moov</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}_;
 *     container for all the metadata
 *   - <b>mvhd</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Mvhd Movie Header
 *       Box}_; overall declarations
 *   - <b>trak</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Trak Track Box}_;
 *       container for an individual track or stream
 *     - <b>tkhd</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Tkhd Track Header
 *         Box}_; overall information about the track
 *     - tref -- _{@see \Vollbehr\Media\Iso14496\Box\Tref Track Reference
 *         Box}_
 *     - edts -- _{@see \Vollbehr\Media\Iso14496\Box\Edts Edit Box}_
 *       - elst -- _{@see \Vollbehr\Media\Iso14496\Box\Elst Edit List Box}_
 *     - <b>mdia</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Mdia Media Box}_
 *       - <b>mdhd</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Mdhd Media Header
 *           Box}_; overall information about the media
 *       - <b>hdlr</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Hdlr Handler
 *           Reference Box}_; declares the media type
 *       - <b>minf</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Minf Media
 *           Information Box}_
 *         - vmhd -- _{@see \Vollbehr\Media\Iso14496\Box\Vmhd Video Media Header
 *             Box}_; overall information (video track only)
 *         - smhd -- _{@see \Vollbehr\Media\Iso14496\Box\Smhd Sound Media Header
 *             Box}_; overall information (sound track only)
 *         - hmhd -- _{@see \Vollbehr\Media\Iso14496\Box\Hmhd Hint Media Header
 *             Box}_; overall information (hint track only)
 *         - nmhd -- _{@see \Vollbehr\Media\Iso14496\Box\Nmhd Null Media Header
 *             Box}_; overall information (some tracks only)
 *         - <b>dinf</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Dinf Data
 *             Information Box}_
 *           - <b>dref</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Dref Data
 *               Reference Box}_
 *         - <b>stbl</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Stbl Sample
 *               Table Box}_
 *           - <b>stsd</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Stsd Sample
 *               Descriptions Box}_
 *           - <b>stts</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Stts Decoding
 *               Time To Sample Box}_
 *           - ctts -- _{@see \Vollbehr\Media\Iso14496\Box\Ctts Composition Time
 *               To Sample Box}_
 *           - <b>stsc</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Stsc Sample To
 *               Chunk Box}_
 *           - stsz -- _{@see \Vollbehr\Media\Iso14496\Box\Stsz Sample Size
 *               Box}_
 *           - stz2 -- _{@see \Vollbehr\Media\Iso14496\Box\Stz2 Compact Sample
 *               Size Box}_
 *           - <b>stco</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Stco Chunk
 *               Offset Box}_; 32-bit
 *           - co64 -- _{@see \Vollbehr\Media\Iso14496\Box\Co64 Chunk Ooffset
 *               Box}_; 64-bit
 *           - stss -- _{@see \Vollbehr\Media\Iso14496\Box\Stss Sync Sample
 *               Table Box}_
 *           - stsh -- _{@see \Vollbehr\Media\Iso14496\Box\Stsh Shadow Sync
 *               Sample Table Box}_
 *           - padb -- _{@see \Vollbehr\Media\Iso14496\Box\Padb Padding Bits
 *               Box}_
 *           - stdp -- _{@see \Vollbehr\Media\Iso14496\Box\Stdp Sample
 *               Degradation Priority Box}_
 *           - sdtp -- _{@see \Vollbehr\Media\Iso14496\Box\Sdtp Independent and
 *               Disposable Samples Box}_
 *           - sbgp -- _{@see \Vollbehr\Media\Iso14496\Box\Sbgp Sample To Group
 *               Box}_
 *           - sgpd -- _{@see \Vollbehr\Media\Iso14496\Box\Sgpd Sample Group
 *               Description}_
 *           - subs -- _{@see \Vollbehr\Media\Iso14496\Box\Subs Sub-Sample
 *               Information Box}_
 *   - mvex -- _{@see \Vollbehr\Media\Iso14496\Box\Mvex Movie Extends Box}_
 *     - mehd -- _{@see \Vollbehr\Media\Iso14496\Box\Mehd Movie Extends Header
 *         Box}_
 *     - <b>trex</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Trex Track Extends
 *         Box}_
 *   - ipmc -- _{@see \Vollbehr\Media\Iso14496\Box\Ipmc IPMP Control Box}_
 * - moof -- _{@see \Vollbehr\Media\Iso14496\Box\Moof Movie Fragment Box}_
 *   - <b>mfhd</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Mfhd Movie Fragment
 *       Header Box}_
 *   - traf -- _{@see \Vollbehr\Media\Iso14496\Box\Traf Track Fragment Box}_
 *     - <b>tfhd</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Tfhd Track Fragment
 *         Header Box}_
 *     - trun -- _{@see \Vollbehr\Media\Iso14496\Box\Trun Track Fragment
 *         Run}_
 *     - sdtp -- _{@see \Vollbehr\Media\Iso14496\Box\Sdtp Independent and
 *         Disposable Samples}_
 *     - sbgp -- _{@see \Vollbehr\Media\Iso14496\Box\Sbgp !SampleToGroup
 *         Box}_
 *     - subs -- _{@see \Vollbehr\Media\Iso14496\Box\Subs Sub-Sample Information
 *         Box}_
 * - mfra -- _{@see \Vollbehr\Media\Iso14496\Box\Mfra Movie Fragment Random
 *     Access Box}_
 *   - tfra -- _{@see \Vollbehr\Media\Iso14496\Box\Tfra Track Fragment Random
 *       Access Box}_
 *   - <b>mfro</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Mfro Movie Fragment
 *       Random Access Offset Box}_
 * - mdat -- _{@see \Vollbehr\Media\Iso14496\Box\Mdat Media Data Box}_
 * - free -- _{@see \Vollbehr\Media\Iso14496\Box\Free Free Space Box}_
 * - skip -- _{@see \Vollbehr\Media\Iso14496\Box\Skip Free Space Box}_
 *   - udta -- _{@see \Vollbehr\Media\Iso14496\Box\Udta User Data Box}_
 *     - cprt -- _{@see \Vollbehr\Media\Iso14496\Box\Cprt Copyright Box}_
 * - meta -- _{@see \Vollbehr\Media\Iso14496\Box\Meta The Meta Box}_
 *   - <b>hdlr</b> -- _{@see \Vollbehr\Media\Iso14496\Box\Hdlr Handler Reference
 *       Box}_; declares the metadata type
 *   - dinf -- _{@see \Vollbehr\Media\Iso14496\Box\Dinf Data Information
 *       Box}_
 *     - dref -- _{@see \Vollbehr\Media\Iso14496\Box\Dref Data Reference
 *         Box}_; declares source(s) of metadata items
 *   - ipmc -- _{@see \Vollbehr\Media\Iso14496\Box\Ipmc IPMP Control Box}_
 *   - iloc -- _{@see \Vollbehr\Media\Iso14496\Box\Iloc Item Location Box}_
 *   - ipro -- _{@see \Vollbehr\Media\Iso14496\Box\Ipro Item Protection Box}_
 *     - sinf -- _{@see \Vollbehr\Media\Iso14496\Box\Sinf Protection Scheme
 *         Information Box}_
 *       - frma -- _{@see \Vollbehr\Media\Iso14496\Box\Frma Original Format
 *           Box}_
 *       - imif -- _{@see \Vollbehr\Media\Iso14496\Box\Imif IPMP Information
 *           Box}_
 *       - schm -- _{@see \Vollbehr\Media\Iso14496\Box\Schm Scheme Type Box}_
 *       - schi -- _{@see \Vollbehr\Media\Iso14496\Box\Schi Scheme Information
 *           Box}_
 *   - iinf -- _{@see \Vollbehr\Media\Iso14496\Box\Iinf Item Information
 *       Box}_
 *     - infe -- _{@see \Vollbehr\Media\Iso14496\Box\Infe Item Information Entry
 *         Box}_
 *   - xml -- _{@see \Vollbehr\Media\Iso14496\Box\Xml XML Box}_
 *   - bxml -- _{@see \Vollbehr\Media\Iso14496\Box\Bxml Binary XML Box}_
 *   - pitm -- _{@see \Vollbehr\Media\Iso14496\Box\Pitm Primary Item Reference
 *       Box}_
 * There are two non-standard extensions to the ISO 14496 standard that add the
 * ability to include file meta information. Both the boxes reside under
 * moov.udta.meta.
 * - _moov_ -- _{@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}_;
 *     container for all the metadata
 * - _udta_ -- _{@see \Vollbehr\Media\Iso14496\Box\Udta User Data Box}_
 * - _meta_ -- _{@see \Vollbehr\Media\Iso14496\Box\Meta The Meta Box}_
 *   - ilst -- _{@see \Vollbehr\Media\Iso14496\Box\Ilst The iTunes/iPod Tag
 *       Container Box}_
 *   - id32 -- _{@see \Vollbehr\Media\Iso14496\Box\Id32 The ID3v2 Box}_
 * @author Sven Vollbehr
 */
final class Iso14496 extends Iso14496\Box
{
    private ?string $_filename = null;

    private bool $_autoClose = false;

    /**
     * Constructs the \Vollbehr\Media\Iso14496 class with given file and options.
     * The following options are currently recognized:
     *   o base -- Indicates that only boxes with the given base path are parsed
     *     from the ISO base media file. Parsing all boxes can possibly have a
     *     significant impact on running time. Base path is a list of nested
     *     boxes separated by a dot. The use of base option implies readonly
     *     option.
     *   o readonly -- Indicates that the file is read from a temporary location
     *     or another source it cannot be written back to.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file,
     *  file descriptor of an opened file, or a {@see \Vollbehr\Io\Reader} instance.
     * @param Array                          $options  The options array.
     */
    public function __construct($filename, $options = [])
    {
        if (isset($options['base'])) {
            $options['readonly'] = true;
        }
        if ($filename instanceof \Vollbehr\Io\Reader) {
            $this->_reader = &$filename;
        } else {

            try {
                $this->_reader    = new \Vollbehr\Io\FileReader($filename);
                $this->_autoClose = true;
            } catch (\Vollbehr\Io\Exception $e) {
                $this->_reader = null;

                throw new Iso14496\Exception($e->getMessage());
            }
            if (is_string($filename) && !isset($options['readonly'])) {
                $this->_filename = $filename;
            }
        }
        $this->setOptions($options);
        $this->setOffset(0);
        $this->setSize($this->_reader->getSize());
        $this->setType('file');
        $this->setContainer(true);
        $this->constructBoxes();
    }

    /**
     * Closes down the reader.
     */
    public function __destruct()
    {
        parent::__destruct();
        if ($this->_autoClose && $this->_reader !== null) {
            $this->_reader->close();
        }
    }

    /**
     * Writes the changes back to given media file.
     * The write operation commits only changes made to the Movie Box. It
     * further changes the order of the Movie Box and Media Data Box in a way
     * compatible for progressive download from a web page.
     * All box offsets must be assumed to be invalid after the write operation.
     * @param string $filename The optional path to the file, use null to save
     *                         to the same file.
     */
    public function write($filename): void
    {
        if ($filename === null && ($filename = $this->_filename) === null) {
            throw new Iso14496\Exception('No file given to write to');
        } elseif ($filename !== null && $this->_filename !== null &&
                   realpath($filename) !== realpath($this->_filename) &&
                   !copy($this->_filename, $filename)) {
            throw new Iso14496\Exception('Unable to copy source to destination: ' .
                 realpath($this->_filename) . '->' . realpath($filename));
        }

        if (($fd = fopen($filename, file_exists($filename) ? 'r+b' : 'wb')) === false) {

            throw new Iso14496\Exception('Unable to open file for writing: ' . $filename);
        }

        /* Calculate file size */
        fseek($fd, 0, SEEK_END);
        $oldFileSize = ftell($fd);
        $oldMoovSize = $this->moov->getSize();
        $this->moov->udta->meta->free->setSize(8);
        $this->moov->udta->meta->hdlr->setHandlerType('mdir');
        $newFileSize = $oldFileSize - $oldMoovSize + $this->moov->getHeapSize();

        /* Calculate free space size */
        if ($oldFileSize < $newFileSize ||
                $this->mdat->getOffset() < $this->moov->getOffset()) {
            // Add constant 4096 bytes for free space to be used later
            $this->moov->udta->meta->free->setSize(8 /* header */ + 4096);
            ftruncate($fd, $newFileSize += 4096);
        } else {
            // Adjust free space to fill up the rest of the space
            $this->moov->udta->meta->free->setSize(8 + $oldFileSize - $newFileSize);
            $newFileSize = $oldFileSize;
        }

        /* Calculate positions */
        if ($this->mdat->getOffset() < $this->moov->getOffset()) {
            $start = $this->mdat->getOffset();
            $until = $this->moov->getOffset();
            $where = $newFileSize;
            $delta = $this->moov->getHeapSize();
        } else {
            $start = $this->moov->getOffset();
            $until = $oldFileSize;
            $where = $newFileSize;
            $delta = $newFileSize - $oldFileSize;
        }

        /* Move data to the end of the file */
        if ($newFileSize != $oldFileSize) {
            for ($i = 1, $cur = $until; $cur > $start; $cur -= 1024, $i++) {
                fseek($fd, $until - (($i * 1024) +
                     ($excess = $cur - 1024 > $start ?
                      0 : $cur - $start - 1024)));
                $buffer = fread($fd, 1024);
                fseek($fd, $where - (($i * 1024) + $excess));
                fwrite($fd, $buffer, 1024);
            }
        }


        /* Update stco/co64 to correspond the data move */
        foreach ($this->moov->getBoxesByIdentifier('trak') as $trak) {
            $chunkOffsetBox        = ($trak->mdia->minf->stbl->stco ?? $trak->mdia->minf->stbl->co64);
            $chunkOffsetTable      = $chunkOffsetBox->getChunkOffsetTable();
            $chunkOffsetTableCount = count($chunkOffsetTable);
            for ($i = 1; $i <= $chunkOffsetTableCount; $i++) {
                $chunkOffsetTable[$i] += $delta;
            }
            $chunkOffsetBox->setChunkOffsetTable($chunkOffsetTable);
        }

        /* Write moov box */
        fseek($fd, $start);
        $this->moov->write(new \Vollbehr\Io\Writer($fd));
        fclose($fd);
    }
}
