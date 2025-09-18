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
 * Sometimes the server from which an audio file is streamed is aware of
 * transmission or coding problems resulting in interruptions in the audio
 * stream. In these cases, the size of the buffer can be recommended by the
 * server using the _Recommended buffer size_ frame. If the embedded info
 * flag is set then this indicates that an ID3 tag with the maximum size
 * described in buffer size may occur in the audio stream. In such case the tag
 * should reside between two MPEG frames, if the audio is MPEG encoded. If the
 * position of the next tag is known, offset to next tag may be used. The offset
 * is calculated from the end of tag in which this frame resides to the first
 * byte of the header in the next. This field may be omitted. Embedded tags are
 * generally not recommended since this could render unpredictable behaviour
 * from present software/hardware.
 * For applications like streaming audio it might be an idea to embed tags into
 * the audio stream though. If the clients connects to individual connections
 * like HTTP and there is a possibility to begin every transmission with a tag,
 * then this tag should include a recommended buffer size frame. If the client
 * is connected to a arbitrary point in the stream, such as radio or multicast,
 * then the recommended buffer size frame should be included in every tag.
 * The buffer size should be kept to a minimum. There may only be one RBUF
 * frame in each tag.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
final class Rbuf extends \Vollbehr\Media\Id3\Frame
{
    /**
     * A flag to denote that an ID3 tag with the maximum size described in
     * buffer size may occur in the audio stream.
     */
    public const EMBEDDED = 0x1;
    /** @var integer */
    private $_bufferSize;

    private int $_infoFlags;

    /** @var integer */
    private $_offset = 0;

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

        // Who designs frames with 3 byte integers??
        $this->_reader = new \Vollbehr\Io\StringReader("\0" . $this->_reader->read($this->_reader->getSize()));

        $this->_bufferSize = $this->_reader->readUInt32BE();
        $this->_infoFlags  = $this->_reader->readInt8();
        if ($this->_reader->available()) {
            $this->_offset = $this->_reader->readInt32BE();
        }
    }

    /**
     * Returns the buffer size.
     * @return integer
     */
    public function getBufferSize()
    {
        return $this->_bufferSize;
    }

    /**
     * Sets the buffer size.
     */
    public function setBufferSize($bufferSize): void
    {
        $this->_bufferSize = $bufferSize;
    }

    /**
     * Checks whether or not the flag is set. Returns <var>true</var> if the
     * flag is set, <var>false</var> otherwise.
     * @param integer $flag The flag to query.
     */
    public function hasInfoFlag($flag): bool
    {
        return ($this->_infoFlags & $flag) == $flag;
    }

    /**
     * Returns the flags byte.
     * @return integer
     */
    public function getInfoFlags()
    {
        return $this->_infoFlags;
    }

    /**
     * Sets the flags byte.
     */
    public function setInfoFlags($infoFlags): void
    {
        $this->_infoFlags = $infoFlags;
    }

    /**
     * Returns the offset to next tag.
     * @return integer
     */
    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * Sets the offset to next tag.
     * @param integer $offset The offset.
     */
    public function setOffset($offset): void
    {
        $this->_offset = $offset;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        $tmp = new \Vollbehr\Io\StringWriter();
        $tmp->writeUInt32BE($this->_bufferSize);

        $writer->write(substr((string) $tmp->toString(), 1, 3))
               ->writeInt8($this->_infoFlags)
               ->writeInt32BE($this->_offset);
    }
}
