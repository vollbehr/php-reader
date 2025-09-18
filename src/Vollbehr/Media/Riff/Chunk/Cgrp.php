<?php

declare(strict_types=1);

namespace Vollbehr\Media\Riff\Chunk;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Compound File Element Group_ chunk stores the actual elements of data referenced by the
 * {@see \Vollbehr\Media\Riff\Chunk\Ctoc CTOC} chunk. The CGRP chunk contains all the compound file elements, concatenated
 * together into one contiguous block of data. Some of the elements in the CGRP chunk might be unused, if the element
 * was marked for deletion or was altered and stored elsewhere within the CGRP chunk.
 * Elements within the CGRP chunk are of arbitrary size and can appear in a specific or arbitrary order, depending upon
 * the file format definition. Each element is identified by a corresponding {@see \Vollbehr\Media\Riff\Chunk\Ctoc CTOC}
 * table entry.
 * @author Sven Vollbehr
 * @todo       Implementation
 */
final class Cgrp extends \Vollbehr\Media\Riff\Chunk
{
    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        throw new \Vollbehr\Media\Riff\Exception('Not yet implemented');
    }
}
