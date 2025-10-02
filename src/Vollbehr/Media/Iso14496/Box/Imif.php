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
 * The _IPMP Information Box_ contains IPMP Descriptors which document the
 * protection applied to the stream.
 * IPMP_Descriptor is defined in 14496-1. This is a part of the MPEG-4 object
 * descriptors (OD) that describe how an object can be accessed and decoded.
 * Here, in the ISO Base Media File Format, IPMP Descriptor can be carried
 * directly in IPMP Information Box without the need for OD stream.
 * The presence of IPMP Descriptor in this box indicates the associated media
 * stream is protected by the IPMP Tool described in the IPMP Descriptor.
 * Each IPMP_Descriptor has an IPMP_ToolID, which identifies the required IPMP
 * tool for protection. An independent registration authority (RA) is used so
 * any party can register its own IPMP Tool and identify this without
 * collisions.
 * The IPMP_Descriptor carries IPMP information for one or more IPMP Tool
 * instances, it includes but not limited to IPMP Rights Data, IPMP Key Data,
 * Tool Configuration Data, etc.
 * More than one IPMP Descriptors can be carried in this box if this media
 * stream is protected by more than one IPMP Tools.
 * @author Sven Vollbehr
 */
final class Imif extends \Vollbehr\Media\Iso14496\Box
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