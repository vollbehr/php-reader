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
 * The _Protection Scheme Information Box_ contains all the information
 * required both to understand the encryption transform applied and its
 * parameters, and also to find other information such as the kind and location
 * of the key management system. It also documents the original (unencrypted)
 * format of the media. The Protection Scheme Info Box is a container Box. It is
 * mandatory in a sample entry that uses a code indicating a protected stream.
 * When used in a protected sample entry, this box must contain the original
 * format box to document the original format. At least one of the following
 * signaling methods must be used to identify the protection applied:
 *  a) MPEG-4 systems with IPMP: no other boxes, when IPMP descriptors in MPEG-4
 *     systems streams are used;
 *  b) Standalone IPMP: an {@see \Vollbehr\Media\Iso14496\Box\Imif IPMP Info Box},
 *     when IPMP descriptors outside MPEG-4 systems are used;
 *  c) Scheme signaling: a {@see \Vollbehr\Media\Iso14496\Box\Schm Scheme Type Box}
 *     and {@see \Vollbehr\Media\Iso14496\Box\Schi Scheme Information Box}, when
 *     these are used (either both must occur, or neither).
 * @author Sven Vollbehr
 */
final class Sinf extends \Vollbehr\Media\Iso14496\Box
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