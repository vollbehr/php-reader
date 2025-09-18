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
 * The _Sample Table Box_ contains all the time and data indexing of the
 * media samples in a track. Using the tables here, it is possible to locate
 * samples in time, determine their type (e.g. I-frame or not), and determine
 * their size, container, and offset into that container.
 * If the track that contains the Sample Table Box references no data, then the
 * Sample Table Box does not need to contain any sub-boxes (this is not a very
 * useful media track).
 * If the track that the Sample Table Box is contained in does reference data,
 * then the following sub-boxes are required:
 * {@see \Vollbehr\Media\Iso14496\Box\Stsd Sample Description},
 * {@see \Vollbehr\Media\Iso14496\Box\Stsz Sample Size},
 * {@see \Vollbehr\Media\Iso14496\Box\Stsc Sample To Chunk}, and
 * {@see \Vollbehr\Media\Iso14496\Box\Stco Chunk Offset}. Further, the
 * {@see \Vollbehr\Media\Iso14496\Box\Stsd Sample Description Box} shall contain at
 * least one entry. A Sample Description Box is required because it contains
 * the data reference index field which indicates which
 * {@see \Vollbehr\Media\Iso14496\Box\Dref Data Reference Box} to use to retrieve
 * the media samples. Without the Sample Description, it is not possible to
 * determine where the media samples are stored. The
 * {@see \Vollbehr\Media\Iso14496\Box\Stss Sync Sample Box} is optional. If the
 * Sync Sample Box is not present, all samples are sync samples.
 * @author Sven Vollbehr
 */
final class Stbl extends \Vollbehr\Media\Iso14496\Box
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