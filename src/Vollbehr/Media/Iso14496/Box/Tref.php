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
 * The _Track Reference Box_ provides a reference from the containing track
 * to another track in the presentation. These references are typed. A
 * {@see \Vollbehr\Media\Iso14496\Box\Hint hint} reference links from the containing
 * hint track to the media data that it hints. A content description reference
 * {@see \Vollbehr\Media\Iso14496\Box\Cdsc cdsc} links a descriptive or metadata
 * track to the content which it describes.
 * Exactly one Track Reference Box can be contained within the
 * {@see \Vollbehr\Media\Iso14496\Box\Trak Track Box}.
 * If this box is not present, the track is not referencing any other track in
 * any way. The reference array is sized to fill the reference type box.
 * @author Sven Vollbehr
 */
final class Tref extends \Vollbehr\Media\Iso14496\Box
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