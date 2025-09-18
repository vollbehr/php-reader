<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf\Object;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Bandwidth Sharing Object_ indicates streams that share bandwidth in
 * such a way that the maximum bandwidth of the set of streams is less than the
 * sum of the maximum bandwidths of the individual streams. There should be one
 * instance of this object for each set of objects that share bandwidth. Whether
 * or not this object can be used meaningfully is content-dependent.
 * @author Sven Vollbehr
 */
final class BandwidthSharing extends \Vollbehr\Media\Asf\BaseObject
{
    public const SHARING_EXCLUSIVE = 'af6060aa-5197-11d2-b6af-00c04fd908e9';
    public const SHARING_PARTIAL   = 'af6060ab-5197-11d2-b6af-00c04fd908e9';

    /** @var string */
    private $_sharingType;

    /** @var integer */
    private $_dataBitrate;

    /** @var integer */
    private $_bufferSize;

    /** @var Array */
    private $_streamNumbers = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader = null, $options);
    }

    /**
     * Returns the type of sharing relationship for this object. Two types are
     * predefined: SHARING_PARTIAL, in which any number of the streams in the
     * relationship may be streaming data at any given time; and
     * SHARING_EXCLUSIVE, in which only one of the streams in the relationship
     * may be streaming data at any given time.
     * @return string
     */
    public function getSharingType()
    {
        return $this->_sharingType;
    }

    /**
     * Sets the type of sharing relationship for this object. Two types are
     * predefined: SHARING_PARTIAL, in which any number of the streams in the
     * relationship may be streaming data at any given time; and
     * SHARING_EXCLUSIVE, in which only one of the streams in the relationship
     * may be streaming data at any given time.
     */
    public function setSharingType($sharingType): void
    {
        $this->_sharingType = $sharingType;
    }

    /**
     * Returns the leak rate R, in bits per second, of a leaky bucket that
     * contains the data portion of all of the streams, excluding all ASF Data
     * Packet overhead, without overflowing. The size of the leaky bucket is
     * specified by the value of the Buffer Size field. This value can be less
     * than the sum of all of the data bit rates in the
     * {@see \Vollbehr\Media\Asf\BaseObject\ExtendedStreamProperties Extended Stream
     * Properties} Objects for the streams contained in this bandwidth-sharing
     * relationship.
     * @return integer
     */
    public function getDataBitrate()
    {
        return $this->_dataBitrate;
    }

    /**
     * Sets the leak rate R, in bits per second, of a leaky bucket that contains
     * the data portion of all of the streams, excluding all ASF Data Packet
     * overhead, without overflowing. The size of the leaky bucket is specified
     * by the value of the Buffer Size field. This value can be less than the
     * sum of all of the data bit rates in the
     * {@see \Vollbehr\Media\Asf\BaseObject\ExtendedStreamProperties Extended Stream
     * Properties} Objects for the streams contained in this bandwidth-sharing
     * relationship.
     * @param integer $dataBitrate The data bitrate.
     */
    public function setDataBitrate($dataBitrate): void
    {
        $this->_dataBitrate = $dataBitrate;
    }

    /**
     * Specifies the size B, in bits, of the leaky bucket used in the Data
     * Bitrate definition. This value can be less than the sum of all of the
     * buffer sizes in the
     * {@see \Vollbehr\Media\Asf\BaseObject\ExtendedStreamProperties Extended Stream
     * Properties} Objects for the streams contained in this bandwidth-sharing
     * relationship.
     * @return integer
     */
    public function getBufferSize()
    {
        return $this->_bufferSize;
    }

    /**
     * Sets the size B, in bits, of the leaky bucket used in the Data Bitrate
     * definition. This value can be less than the sum of all of the buffer
     * sizes in the
     * {@see \Vollbehr\Media\Asf\BaseObject\ExtendedStreamProperties Extended Stream
     * Properties} Objects for the streams contained in this bandwidth-sharing
     * relationship.
     * @param integer $bufferSize The buffer size.
     */
    public function setBufferSize($bufferSize): void
    {
        $this->_bufferSize = $bufferSize;
    }

    /**
     * Returns an array of stream numbers.
     * @return Array
     */
    public function getStreamNumbers()
    {
        return $this->_streamNumbers;
    }

    /**
     * Sets the array of stream numbers.
     * @param Array $streamNumbers The array of stream numbers.
     */
    public function setStreamNumbers($streamNumbers): void
    {
        $this->_streamNumbers = $streamNumbers;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $streamNumbersCount = count($this->_streamNumber);
        $this->setSize(24 /* for header */ + 28 + $streamNumbersCount * 2);

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_sharingType)
               ->writeUInt32LE($this->_dataBitrate)
               ->writeUInt32LE($this->_bufferSize)
               ->writeUInt16LE($streamNumbersCount);
        for ($i = 0; $i < $streamNumbersCount; $i++) {
            $writer->writeUInt16LE($this->_streamNumbers[$i]);
        }
    }
}
