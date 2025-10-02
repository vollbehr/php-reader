<?php

declare(strict_types=1);

namespace Vollbehr\Media\Flac\MetadataBlock;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents the application metadata block. This block is for use by third-party applications. The only
 * mandatory field is a 32-bit identifier. This ID is granted upon request to an application by the FLAC maintainers.
 * The remainder is of the block is defined by the registered application. Visit the registration page if you would like
 * to register an ID for your application with FLAC.
 * Applications can be registered at {@see http://flac.sourceforge.net/id.html}.
 * @author Sven Vollbehr
 */
final class Application extends \Vollbehr\Media\Flac\MetadataBlock
{
    /**
     * Constructs the class with given parameters and parses object related data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_identifier = $this->_reader->readUInt32BE();
        $this->_data       = $this->_reader->read($this->getSize() - 4);
    }

    /**
     * Returns the application identifier.
     * @return integer
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Returns the application data.
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }
}
