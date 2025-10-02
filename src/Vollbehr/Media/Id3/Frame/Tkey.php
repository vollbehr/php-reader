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
 * The _Initial key_ frame contains the musical key in which the sound
 * starts. It is represented as a string with a maximum length of three
 * characters. The ground keys are represented with 'A', 'B', 'C', 'D', 'E', 'F'
 * and 'G' and halfkeys represented with 'b' and '#'. Minor is represented as
 * 'm', e.g. 'Dbm'. Off key is represented with an 'o' only.
 * @author Sven Vollbehr
 */
final class Tkey extends \Vollbehr\Media\Id3\TextFrame
{
}
