<?php

declare(strict_types=1);

namespace Vollbehr\Media\Riff\Chunk;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * The _ID3 Tag_ chunk contains an {@see \Vollbehr\Media\Id3v2 ID3v2} tag.
 * @author Sven Vollbehr
 */
final class Id3 extends \Vollbehr\Media\Riff\Chunk
{
    private \Vollbehr\Media\Id3v2 $_tag;

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_tag = new \Vollbehr\Media\Id3v2($this->_reader, ['readonly' => true]);
    }

    /**
     * Returns the {@see \Vollbehr\Media\Id3v2 Id3v2} tag class instance.
     * @return string
     */
    public function getTag(): \Vollbehr\Media\Id3v2
    {
        return $this->_tag;
    }

    /**
     * Sets the {@see \Vollbehr\Media\Id3v2 Id3v2} tag class instance.
     * @param \Vollbehr\Media\Id3v2 $tag The tag instance.
     */
    public function setTag(\Vollbehr\Media\Id3v2 $tag): void
    {
        $this->_tag = $tag;
    }
}
