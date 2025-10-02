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
 * Within the _Track Fragment Box_ there is a set of track fragments, zero
 * or more per track. The track fragments in turn contain zero or more track
 * runs, each of which document a contiguous run of samples for that track.
 * Within these structures, many fields are optional and can be defaulted. It is
 * possible to add empty time to a track using these structures, as well as
 * adding samples. Empty inserts can be used in audio tracks doing silence
 * suppression, for example.
 * @author Sven Vollbehr
 */
final class Traf extends \Vollbehr\Media\Iso14496\Box
{
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
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