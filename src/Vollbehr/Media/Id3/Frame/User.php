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
 * The _Terms of use frame_ contains a brief description of the terms of
 * use and ownership of the file. More detailed information concerning the legal
 * terms might be available through the {@see \Vollbehr\Media\Id3\Frame\Wcop WCOP}
 * frame. Newlines are allowed in the text. There may be more than one Terms of
 * use frames in a tag, but only one with the same language.
 * @author Sven Vollbehr
 */
final class User extends \Vollbehr\Media\Id3\LanguageTextFrame
{
}
