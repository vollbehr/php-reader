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
 * The _Meta Box_ contains descriptive or annotative metadata. The
 * _meta_ box is required to contain a
 * {@see \Vollbehr\Media\Iso14496\Box\Hdlr hdlr} box indicating the structure or
 * format of the _meta_ box contents. That metadata is located either
 * within a box within this box (e.g. an XML box), or is located by the item
 * identified by a primary item box.
 * All other contained boxes are specific to the format specified by the handler
 * box.
 * The other boxes defined here may be defined as optional or mandatory for a
 * given format. If they are used, then they must take the form specified here.
 * These optional boxes include a data-information box, which documents other
 * files in which metadata values (e.g. pictures) are placed, and a item
 * location box, which documents where in those files each item is located (e.g.
 * in the common case of multiple pictures stored in the same file). At most one
 * meta box may occur at each of the file level, movie level, or track level.
 * If an {@see \Vollbehr\Media\Iso14496\Box\Ipro Item Protection Box} occurs, then
 * some or all of the meta-data, including possibly the primary resource, may
 * have been protected and be un-readable unless the protection system is taken
 * into account.
 * @author Sven Vollbehr
 */
final class Meta extends \Vollbehr\Media\Iso14496\FullBox
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