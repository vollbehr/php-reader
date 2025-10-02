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
 * The _Unique File Identifier frame_'s purpose is to be able to identify
 * the audio file in a database, that may provide more information relevant to
 * the content. Since standardisation of such a database is beyond this document,
 * all UFID frames begin with an 'owner identifier' field. It is a null-
 * terminated string with a URL [URL] containing an email address, or a link to
 * a location where an email address can be found, that belongs to the
 * organisation responsible for this specific database implementation.
 * Questions regarding the database should be sent to the indicated email
 * address. The URL should not be used for the actual database queries. The
 * string "http://www.id3.org/dummy/ufid.html" should be used for tests. The
 * 'Owner identifier' must be non-empty (more than just a termination). The
 * 'Owner identifier' is then followed by the actual identifier, which may be
 * up to 64 bytes. There may be more than one "UFID" frame in a tag, but only
 * one with the same 'Owner identifier'.
 * @author Sven Vollbehr
 * @author Arlo Kleijweg
 */
final class Ufid extends \Vollbehr\Media\Id3\Frame
{
    /** @var string */
    private $_owner;

    /** @var string */
    private $_fileIdentifier;

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

        [$this->_owner, $this->_fileIdentifier] = $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 2);
    }

    /**
     * Returns the owner identifier string.
     * @return string
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Sets the owner identifier string.
     * @param string $owner The owner identifier string.
     */
    public function setOwner($owner): void
    {
        $this->_owner = $owner;
    }

    /**
     * Returns the identifier binary data associated with the frame.
     * @return string
     */
    public function getFileIdentifier()
    {
        return $this->_fileIdentifier;
    }

    /**
     * Sets the identifier binary data associated with the frame.
     * @param string $fileIdentifier The file identifier binary data string.
     */
    public function setFileIdentifier($fileIdentifier): void
    {
        $this->_fileIdentifier = $fileIdentifier;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $writer->writeString8($this->_owner, 1)
               ->write($this->_fileIdentifier);
    }
}
