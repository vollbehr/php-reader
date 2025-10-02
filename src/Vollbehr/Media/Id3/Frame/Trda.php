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
 * The _Recording dates_ frame is intended to be used as complement to
 * the {@see \Vollbehr\Media\Id3\Frame\Tyer TYER},
 * {@see \Vollbehr\Media\Id3\Frame\Tdat TDAT} and
 * {@see \Vollbehr\Media\Id3\Frame\Time TIME} frames. E.g. '4th-7th June, 12th June'
 * in combination with the {@see \Vollbehr\Media\Id3\Frame\Tyer TYER} frame.
 * @author Sven Vollbehr
 * @deprecated ID3v2.3.0
 */
final class Trda extends \Vollbehr\Media\Id3\TextFrame
{
}
