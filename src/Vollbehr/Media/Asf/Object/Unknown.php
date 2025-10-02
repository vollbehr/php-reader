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
 * The _Unknown Object_ represents objects that are not known to the
 * library.
 * @author Sven Vollbehr
 */
final class Unknown extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var string */
    private $_data;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_data = $this->_reader->read($this->getSize() - 24 /* for header */);
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->write($this->_data);
    }
}
