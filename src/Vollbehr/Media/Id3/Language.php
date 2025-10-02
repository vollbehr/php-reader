<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * The <var>\Vollbehr\Media\Id3\Language</var> interface implies that the
 * implementing ID3v2 frame supports its content to be given in multiple
 * languages.
 * The three byte language code is used to describe the language of the frame's
 * content, according to {@see http://www.loc.gov/standards/iso639-2/
 * ISO-639-2}. The language should be represented in lower case. If the language
 * is not known the string 'und' should be used.
 * @author Sven Vollbehr
 */
interface Language
{
    /**
     * Returns the text language code.
     * @return string
     */
    public function getLanguage();
    /**
     * Sets the text language code.
     * @param string $language The text language code.
     */
    public function setLanguage($language);
}