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
 * The _Data Object_ contains all of the _Data Packet_s for a file.
 * These Data Packets are organized in terms of increasing send times. A _Data
 * Packet_ can contain interleaved data from several digital media streams.
 * This data can consist of entire objects from one or more streams.
 * Alternatively, it can consist of partial objects (fragmentation).
 * Capabilities provided within the interleave packet definition include:
 *   o Single or multiple payload types per Data Packet
 *   o Fixed-size Data Packets
 *   o Error correction information (optional)
 *   o Clock information (optional)
 *   o Redundant sample information, such as presentation time stamp (optional)
 * Please note that the data packets are not parsed.
 * @author Sven Vollbehr
 */
final class Data extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
    private $_fileId;

    /** @var integer */
    private $_totalDataPackets;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_fileId           = $this->_reader->readGuid();
        $this->_totalDataPackets = $this->_reader->readInt64LE();
        $this->_reader->skip(2);

        //      No support for Data Packets as of yet (if ever)
        //      for ($i = 0; $i < $this->_totalDataPackets; $i++)
        //        $this->_dataPackets[] =
        //            new \Vollbehr\Media\Asf\BaseObject\Data\Packet($reader);
    }

    /**
     * Returns the unique identifier for this ASF file. The value of this field
     * is changed every time the file is modified in any way. The value of this
     * field is identical to the value of the _File ID_ field of the
     * _Header Object_.
     * @return string
     */
    public function getFileId()
    {
        return $this->_fileId;
    }

    /**
     * Returns the number of ASF Data Packet entries that exist within the
     * _Data Object_. It must be equal to the _Data Packet Count_
     * field in the _File Properties Object_. The value of this field is
     * invalid if the broadcast flag field of the _File Properties Object_
     * is set to 1.
     * @return integer
     */
    public function getTotalDataPackets()
    {
        return $this->_totalDataPackets;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): never
    {
        throw new \Vollbehr\Media\Asf\Exception('Operation not supported');
    }
}
