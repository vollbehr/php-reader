<?php

declare(strict_types=1);

namespace Vollbehr\Media\Vorbis;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * A Vorbis bitstream begins with three header packets. The header packets are, in order, the identication header, the
 * comments header, and the setup header. All are required for decode compliance. This class is the base class for all
 * these headers.
 * @author Sven Vollbehr
 */
abstract class Header
{
    /**
     * The reader object.
     * @var Reader
     */
    protected $_reader;
    /**
     * The packet type; the identication header is type 1, the comment header type 3 and the setup header type 5.
     * @var Array
     */
    protected $_packetType;
    /** $var integer */
    protected $_packetSize = 0;

    /**
     * Constructs the class with given parameters.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        $this->_reader = $reader;
        if (!in_array($this->_packetType = $this->_reader->readUInt8(), [1, 3, 5])) {

            throw new Exception('Unknown header packet type: ' . $this->_packetType);
        }
        if (($vorbis = $this->_reader->read(6)) != 'vorbis') {

            throw new Exception('Unknown header packet: ' . $vorbis);
        }

        $skipBytes = $this->_reader->getCurrentPagePosition();
        for ($page = $this->_reader->getCurrentPageNumber(); /* goes on until we find packet end */; $page++) {
            $segments = $this->_reader->getPage($page)->getSegmentTable();
            $counter  = count($segments);
            for ($i = 0, $skippedSegments = 0; $i < $counter; $i++) {
                // Skip page segments that are already read in
                if ($skipBytes > $segments[$i]) {
                    $skipBytes -= $segments[$i];
                    continue;
                }

                // Skip segments that are full
                if ($segments[$i] == 255 && ++$skippedSegments) {
                    continue;
                }

                // Record packet size from the first non-255 segment
                $this->_packetSize += $i * 255 + $segments[$i];
                break 2;
            }
            $this->_packetSize += $skippedSegments * 255;
        }
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

            throw new Exception('Unknown field: ' . $name);
        }
    }
}
