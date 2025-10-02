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
 * The _Language_ frame should contain the languages of the text or lyrics
 * spoken or sung in the audio. The language is represented with three
 * characters according to {@see http://www.loc.gov/standards/iso639-2/
 * ISO-639-2}. If more than one language is used in the text their language
 * codes should follow according to the amount of their usage.
 * @author Sven Vollbehr
 */
final class Tlan extends \Vollbehr\Media\Id3\TextFrame
{
}
