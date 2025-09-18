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
 * The _Linked information_ frame is used to keep information duplication
 * as low as possible by linking information from another ID3v2 tag that might
 * reside in another audio file or alone in a binary file. It is recommended
 * that this method is only used when the files are stored on a CD-ROM or other
 * circumstances when the risk of file separation is low.
 * Data should be retrieved from the first tag found in the file to which this
 * link points. There may be more than one LINK frame in a tag, but only one
 * with the same contents.
 * A linked frame is to be considered as part of the tag and has the same
 * restrictions as if it was a physical part of the tag (i.e. only one
 * {@see \Vollbehr\Media\Id3\Frame\Rvrb RVRB} frame allowed, whether it's linked or
 * not).
 * Frames that may be linked and need no additional data are
 * {@see \Vollbehr\Media\Id3\Frame\Aspi ASPI},
 * {@see \Vollbehr\Media\Id3\Frame\Etco ETCO},
 * {@see \Vollbehr\Media\Id3\Frame\Equ2 EQU2},
 * {@see \Vollbehr\Media\Id3\Frame\Mcdi MCDI},
 * {@see \Vollbehr\Media\Id3\Frame\Mllt MLLT},
 * {@see \Vollbehr\Media\Id3\Frame\Owne OWNE},
 * {@see \Vollbehr\Media\Id3\Frame\Rva2 RVA2},
 * {@see \Vollbehr\Media\Id3\Frame\Rvrb RVRB},
 * {@see \Vollbehr\Media\Id3\Frame\Sytc SYTC}, the text information frames (ie
 * frames descendats of {@see \Vollbehr\Media\Id3\TextFrame}) and the URL
 * link frames (ie frames descendants of
 * {@see \Vollbehr\Media\Id3\LinkFrame}).
 * The {@see \Vollbehr\Media\Id3\Frame\Aenc AENC},
 * {@see \Vollbehr\Media\Id3\Frame\Apic APIC},
 * {@see \Vollbehr\Media\Id3\Frame\Geob GEOB}
 * and {@see \Vollbehr\Media\Id3\Frame\Txxx TXXX} frames may be linked with the
 * content descriptor as additional ID data.
 * The {@see \Vollbehr\Media\Id3\Frame\User USER} frame may be linked with the
 * language field as additional ID data.
 * The {@see \Vollbehr\Media\Id3\Frame\Priv PRIV} frame may be linked with the owner
 * identifier as additional ID data.
 * The {@see \Vollbehr\Media\Id3\Frame\Comm COMM},
 * {@see \Vollbehr\Media\Id3\Frame\Sylt SYLT} and
 * {@see \Vollbehr\Media\Id3\Frame\Uslt USLT} frames may be linked with three bytes
 * of language descriptor directly followed by a content descriptor as
 * additional ID data.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Link extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_target;

    /** @var string */
    private $_url;

    /** @var string */
    private $_qualifier;

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

        $this->_target                   = $this->_reader->read(4);
        [$this->_url, $this->_qualifier] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
    }

    /**
     * Returns the target tag identifier.
     * @return string
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Sets the target tag identifier.
     * @param string $target The target tag identifier.
     */
    public function setTarget($target): void
    {
        $this->_target = $target;
    }

    /**
     * Returns the target tag URL.
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Sets the target tag URL.
     * @param string $url The target URL.
     */
    public function setUrl($url): void
    {
        $this->_url = $url;
    }

    /**
     * Returns the additional data to identify further the tag.
     * @return string
     */
    public function getQualifier()
    {
        return $this->_qualifier;
    }

    /**
     * Sets the additional data to be used in tag identification.
     */
    public function setQualifier($qualifier): void
    {
        $this->_qualifier = $qualifier;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8(substr($this->_target, 0, 4), 4)
               ->writeString8($this->_url, 1)
               ->writeString8($this->_qualifier);
    }
}
