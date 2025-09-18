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
 * This non-standard frame is used by iTunes in ID3v2.3.0 for sorting the names of the
 * Album Artist(s) of a track, that specified in the "TPE1" frame.
 * @author Darren Burnhill
 */
final class Tso2 extends \Vollbehr\Media\Id3\TextFrame
{
}
