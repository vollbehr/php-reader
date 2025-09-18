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
 * The _Edit Box_ maps the presentation time-line to the media time-line as
 * it is stored in the file. The Edit Box is a container for the edit lists.
 * The Edit Box is optional. In the absence of this box, there is an implicit
 * one-to-one mapping of these time-lines, and the presentation of a track
 * starts at the beginning of the presentation. An empty edit is used to offset
 * the start time of a track.
 * @author Sven Vollbehr
 */
final class Edts extends \Vollbehr\Media\Iso14496\Box
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