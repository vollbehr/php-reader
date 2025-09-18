<?php

declare(strict_types=1);

namespace Vollbehr\Media\Flac\MetadataBlock;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents the padding metadata block. This block allows for an arbitrary amount of padding. The contents
 * of a PADDING block have no meaning. This block is useful when it is known that metadata will be edited after
 * encoding; the user can instruct the encoder to reserve a PADDING block of sufficient size so that when metadata is
 * added, it will simply overwrite the padding (which is relatively quick) instead of having to insert it into the right
 * place in the existing file (which would normally require rewriting the entire file).
 * @author Sven Vollbehr
 */
final class Padding extends \Vollbehr\Media\Flac\MetadataBlock
{
    /**
     * Constructs the class with given parameters and parses object related data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
    }
}
