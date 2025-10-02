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
 * The _Movie Extends Box_ warns readers that there might be
 * {@see \Vollbehr\Media\Iso14496\Box\Mfra Movie Fragment Boxes} in this file. To
 * know of all samples in the tracks, these Movie Fragment Boxes must be found
 * and scanned in order, and their information logically added to that found in
 * the {@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}.
 * @author Sven Vollbehr
 */
final class Mvex extends \Vollbehr\Media\Iso14496\Box
{
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setContainer(true);
        if ($reader === null) {
            return;
        }

        $this->constructBoxes();
    }
}