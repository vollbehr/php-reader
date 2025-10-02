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
 * The _Copyright message_ frame, in which the string must begin with a
 * year and a space character (making five characters), is intended for the
 * copyright holder of the original sound, not the audio file itself. The
 * absence of this frame means only that the copyright information is
 * unavailable or has been removed, and must not be interpreted to mean that the
 * audio is public domain. Every time this field is displayed the field must be
 * preceded with 'Copyright ' (C) ' ', where (C) is one character showing a C in
 * a circle.
 * @author Sven Vollbehr
 */
final class Tcop extends \Vollbehr\Media\Id3\TextFrame
{
}
