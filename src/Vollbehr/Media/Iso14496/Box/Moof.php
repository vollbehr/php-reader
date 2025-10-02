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
 * The _Movie Fragment Box_ extend the presentation in time. They provide
 * the information that would previously have been in the
 * {@see \Vollbehr\Media\Iso14496\Box\Moov Movie Box}. The actual samples are in
 * {@see \Vollbehr\Media\Iso14496\Box\Mdat Media Data Boxes}, as usual, if they are
 * in the same file. The data reference index is in the sample description, so
 * it is possible to build incremental presentations where the media data is in
 * files other than the file containing the Movie Box.
 * The Movie Fragment Box is a top-level box, (i.e. a peer to the Movie Box and
 * Media Data boxes). It contains a
 * {@see \Vollbehr\Media\Iso14496\Box\Mfhd Movie Fragment Header Box}, and then one
 * or more {@see \Vollbehr\Media\Iso14496\Box\Traf Track Fragment Boxes}.
 * @author Sven Vollbehr
 */
final class Moof extends \Vollbehr\Media\Iso14496\Box
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