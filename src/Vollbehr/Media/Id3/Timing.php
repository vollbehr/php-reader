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
 * The <var>\Vollbehr\Media\Id3\Timing</var> interface implies that the implementing
 * ID3v2 frame contains one or more 32-bit timestamps.
 * The timestamps are absolute times, meaning that every stamp contains the time
 * from the beginning of the file.
 * @author Sven Vollbehr
 */
interface Timing
{
    /**
     * The timestamp is an absolute time, using MPEG frames as unit.
     */
    public const MPEG_FRAMES = 1;

    /**
     * The timestamp is an absolute time, using milliseconds as unit.
     */
    public const MILLISECONDS = 2;

    /**
     * Returns the timing format.
     * @return integer
     */
    public function getFormat();
    /**
     * Sets the timing format.
     * @param integer $format The timing format.
     */
    public function setFormat($format);
}