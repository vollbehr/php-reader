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
 * The <var>Encoding</var> interface implies that the implementing ID3v2 frame
 * supports content encoding.
 * @author Sven Vollbehr
 */
interface Encoding
{
    /**
     * The ISO-8859-1 encoding.
     */
    public const ISO88591 = 0;

    /**
     * The UTF-16 Unicode encoding with BOM.
     */
    public const UTF16 = 1;

    /**
     * The UTF-16LE Unicode encoding without BOM.
     */
    public const UTF16LE = 4;

    /**
     * The UTF-16BE Unicode encoding without BOM.
     */
    public const UTF16BE = 2;

    /**
     * The UTF-8 Unicode encoding.
     */
    public const UTF8 = 3;

    /**
     * Returns the text encoding.
     * All the strings read from a file are automatically converted to the
     * character encoding specified with the <var>encoding</var> option. See
     * {@see \Vollbehr\Media\Id3v2} for details. This method returns that character
     * encoding, or any value set after read, translated into a string form
     * regarless if it was set using a {@see \Vollbehr\Media\Id3\Encoding} constant
     * or a string.
     * @return integer
     */
    public function getEncoding();
    /**
     * Sets the text encoding.
     * All the string written to the frame are done so using given character
     * encoding. No conversions of existing data take place upon the call to
     * this method thus all texts must be given in given character encoding.
     * The character encoding parameter takes either a
     * {@see \Vollbehr\Media\Id3\Encoding} constant or a character set name string
     * in the form accepted by iconv. The default character encoding used to
     * write the frame is 'utf-8'.
     * @see Encoding
     * @param integer $encoding The text encoding.
     */
    public function setEncoding($encoding);
}