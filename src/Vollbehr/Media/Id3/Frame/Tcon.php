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
 * The _Content type_, which ID3v1 was stored as a one byte numeric value
 * only, is now a string. You may use one or several of the ID3v1 types as
 * numerical strings, or, since the category list would be impossible to
 * maintain with accurate and up to date categories, define your own.
 * You may also use any of the following keywords:
 * <pre>
 *  RX  Remix
 *  CR  Cover
 * </pre>
 * @author Sven Vollbehr
 */
final class Tcon extends \Vollbehr\Media\Id3\TextFrame
{
}
