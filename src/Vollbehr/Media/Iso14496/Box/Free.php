<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The contents of a _Free Space Box_ are irrelevant and may be ignored, or
 * the object deleted, without affecting the presentation. (Care should be
 * exercised when deleting the object, as this may invalidate the offsets used
 * in the sample table, unless this object is after all the media data).
 * @author Sven Vollbehr
 */
final class Free extends \Vollbehr\Media\Iso14496\Box
{
    /**
     * Constructs the class with given parameters.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return ($this->getSize() >= 8 ? $this->getSize() : 0);
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        if ($this->getSize() >= 8) {
            parent::_writeData($writer);
            $writer->write(str_repeat("\0", $this->getSize() - 8));
        }
    }
}
