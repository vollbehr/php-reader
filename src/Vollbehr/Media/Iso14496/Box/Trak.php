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
 * The _Track Box_ is a container box for a single track of a presentation.
 * A presentation consists of one or more tracks. Each track is independent of
 * the other tracks in the presentation and carries its own temporal and spatial
 * information. Each track will contain its associated
 * {@see \Vollbehr\Media\Iso14496\Box\Mdia Media Box}.
 * Tracks are used for two purposes:
 *  (a) to contain media data (media tracks) and
 *  (b) to contain packetization information for streaming protocols
 *      (hint tracks).
 * There shall be at least one media track within an ISO file, and all the media
 * tracks that contributed to the hint tracks shall remain in the file, even if
 * the media data within them is not referenced by the hint tracks; after
 * deleting all hint tracks, the entire un-hinted presentation shall remain.
 * @author Sven Vollbehr
 */
final class Trak extends \Vollbehr\Media\Iso14496\Box
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