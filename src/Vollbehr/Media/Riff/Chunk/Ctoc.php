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
 * The _Compound File Table of Contents_ chunk functions mainly as an index, allowing direct access to elements
 * within a compound file. The CTOC chunk also contains information about the attributes of the entire file and of each
 * media element within the file.
 * To provide the maximum flexibility for defining compound file formats, the CTOC chunk can be customized at several
 * levels. The CTOC chunk contains fields whose length and usage is defined by other CTOC fields. This parameterization
 * adds complexity, but it provides flexibility to file format designers and allows applications to correctly read data
 * without necessarily knowing the specific file format definition.
 * @author Sven Vollbehr
 * @todo       Implementation
 */
final class Ctoc extends \Vollbehr\Media\Riff\Chunk
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
