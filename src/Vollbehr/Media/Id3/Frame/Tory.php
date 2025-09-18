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
 * The _Original release year_ frame is intended for the year when the
 * original recording, if for example the music in the file should be a cover of
 * a previously released song, was released. The field is formatted as in the
 * {@see \Vollbehr\Media\Id3\Frame\Tyer TYER} frame.
 * @author Sven Vollbehr
 * @deprecated ID3v2.3.0
 */
final class Tory extends \Vollbehr\Media\Id3\DateFrame
{
    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options, 'Y');
    }
    /**
     * Returns the year.
     */
    public function getYear(): int
    {
        return intval($this->getText());
    }
    /**
     * Sets the year.
     * @param integer $year The year given in four digits.
     */
    public function setYear($year): void
    {
        $this->setText(strval($year));
    }
}
