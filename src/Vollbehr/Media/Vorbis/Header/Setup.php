<?php

declare(strict_types=1);

namespace Vollbehr\Media\Vorbis\Header;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The setup header contains the bulk of the codec setup information needed for decode. The setup header contains, in
 * order, the lists of codebook configurations, time-domain transform configurations (placeholders in Vorbis I), floor
 * configurations, residue configurations, channel mapping configurations and mode configurations.
 * @author Sven Vollbehr
 * @todo       Implementation
 */
final class Setup extends \Vollbehr\Media\Vorbis\Header
{
    /**
     * Constructs the class with given parameters.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_reader->skip($this->_packetSize - 7 /* header */);
    }
}
