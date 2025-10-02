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
 * The _File type_ frame indicates which type of audio this tag defines.
 * The following types and refinements are defined:
 * <pre>
 * MIME   MIME type follows
 *  MPG    MPEG Audio
 *    /1     MPEG 1/2 layer I
 *    /2     MPEG 1/2 layer II
 *    /3     MPEG 1/2 layer III
 *    /2.5   MPEG 2.5
 *    /AAC   Advanced audio compression
 *  VQF    Transform-domain Weighted Interleave Vector Quantisation
 *  PCM    Pulse Code Modulated audio
 * </pre>
 * but other types may be used, but not for these types though. This is used in
 * a similar way to the predefined types in the
 * {@see \Vollbehr\Media\Id3\Frame\Tmed TMED} frame. If this frame is not present
 * audio type is assumed to be MPG.
 * @author Sven Vollbehr
 */
final class Tflt extends \Vollbehr\Media\Id3\TextFrame
{
}
