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
 * The _Padding Object_ is a dummy object that is used to pad the size of
 * the _Header Object_. This object enables the size of any object stored
 * in the _Header Object_ to grow or shrink without having to rewrite the
 * entire _Data Object_ and _Index Object_ sections of the ASF file.
 * For instance, if entries in the _Content Description Object_ or
 * _Extended Content Description Object_ need to be removed or shortened,
 * the size of the _Padding Object_ can be increased to compensate for the
 * reduction in size of the _Content Description Object_. The ASF file can
 * then be updated by overwriting the previous _Header Object_ with the
 * edited _Header Object_ of identical size, without having to move or
 * rewrite the data contained in the _Data Object_.
 * @author Sven Vollbehr
 */
final class Padding extends \Vollbehr\Media\Asf\BaseObject
{
    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        if ($this->getSize() == 0) {
            $this->setSize(24);
        }
        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->write(str_pad('', $this->getSize() - 24 /* header */, "\0"));
    }
}
