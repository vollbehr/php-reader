<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3\Frame;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This non-standard frame is used by iTunes in ID3v2.3.0 to denote a track as being part
 * of a compilation. Examples would be "Various Artists" or "Greatest Hits" releases.
 * @author Darren Burnhill
 */
final class Tcmp extends \Vollbehr\Media\Id3\NumberFrame
{
}
