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
 * The _Movie Fragment Random Access Box_ provides a table which may assist
 * readers in finding random access points in a file using movie fragments. It
 * contains a track fragment random access box for each track for which
 * information is provided (which may not be all tracks). It is usually placed
 * at or near the end of the file; the last box within the Movie Fragment Random
 * Access Box provides a copy of the length field from the Movie Fragment Random
 * Access Box. Readers may attempt to find this box by examining the last 32
 * bits of the file, or scanning backwards from the end of the file for a Movie
 * Fragment Random Access Offset Box and using the size information in it, to
 * see if that locates the beginning of a Movie Fragment Random Access Box.
 * This box provides only a hint as to where random access points are; the movie
 * fragments themselves are definitive. It is recommended that readers take care
 * in both locating and using this box as modifications to the file after it was
 * created may render either the pointers, or the declaration of random access
 * points, incorrect.
 * @author Sven Vollbehr
 */
final class Mfra extends \Vollbehr\Media\Iso14496\Box
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