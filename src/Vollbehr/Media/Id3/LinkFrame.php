<?php

declare(strict_types=1);

namespace Vollbehr\Media\Id3;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * A base class for all the URL link frames.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
abstract class LinkFrame extends Frame
{
    /** @var string */
    protected $_link;

    /**
     * Constructs the class with given parameters and parses object related
     * data.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     * @param Array $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($this->_reader !== null) {
            $this->_link = implode('', $this->_explodeString8($this->_reader->read($this->_reader->getSize()), 1));
        }
    }

    /**
     * Returns the link associated with the frame.
     * @return string
     */
    public function getLink()
    {
        return $this->_link;
    }

    /**
     * Sets the link. The link encoding is always ISO-8859-1.
     * @param string $link The link.
     */
    public function setLink($link): void
    {
        $this->_link = $link;
    }

    /**
     * Writes the frame raw data without the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    protected function _writeData($writer)
    {
        $writer->write($this->_link);
    }
}
