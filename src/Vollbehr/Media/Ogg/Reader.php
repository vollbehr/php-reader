<?php

declare(strict_types=1);

namespace Vollbehr\Media\Ogg;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */



/**#@-*/

/**
 * This class is a \Vollbehr\Io\Reader specialization that can read a file containing the Ogg bitstream format version 0 as
 * described in {@see http://tools.ietf.org/html/rfc3533 RFC3533}. It is a general, freely-available encapsulation
 * format for media streams. It is able to encapsulate any kind and number of video and audio encoding formats as well
 * as other data streams in a single bitstream.
 * @author Sven Vollbehr
 * @todo       Currently supports only one logical bitstream
 */
final class Reader extends \Vollbehr\Io\Reader
{
    private array $_pages = [];

    private int $_currentPageNumber = 0;

    private float | int $_currentPagePosition = 0;

    /**
     * Constructs the Ogg class with given file.
     * @param string $filename The path to the file.
     * @throws \Vollbehr\Io\Exception if an error occur in stream handling.
     * @throws Exception if an error occurs in Ogg bitstream reading.
     */
    public function __construct($filename)
    {
        $reader   = new \Vollbehr\Io\FileReader($filename);
        $fileSize = $reader->getSize();
        while ($reader->getOffset() < $fileSize) {
            $this->_pages[] = [
                'offset' => $reader->getOffset(),
                'page' => $page = new Page($reader),
            ];
            $this->_size += $page->getPageSize();
            $reader->skip($page->getPageSize());
        }
        $reader->setOffset($this->_pages[$this->_currentPageNumber]['offset'] +
             $this->_pages[$this->_currentPageNumber]['page']->getHeaderSize());
        $this->_fd = $reader->getFileDescriptor();
    }

    /**
     * Overwrite the method to return the current point of operation within the Ogg bitstream.
     * @throws \Vollbehr\Io\Exception if an I/O error occurs
     * @return integer
     */
    public function getOffset(): int | float
    {
        $offset = 0;
        for ($i = 0; $i < $this->_currentPageNumber; $i++) {
            $offset += $this->_pages[$i]['page']->getPageSize();
        }

        return $offset += $this->_currentPagePosition;
    }

    /**
     * Overwrite the method to set the point of operation within the Ogg bitstream.
     * @param integer $offset The new point of operation.
     * @throws \Vollbehr\Io\Exception if an I/O error occurs
     */
    public function setOffset($offset): void
    {
        $streamSize = 0;
        for ($i = 0, $pageCount = count($this->_pages); $i < $pageCount; $i++) {
            if (($streamSize + $this->_pages[$i]['page']->getPageSize()) >= $offset) {
                $this->_currentPageNumber   = $i;
                $this->_currentPagePosition = $offset - $streamSize;
                parent::setOffset($this->_pages[$i]['offset'] + $this->_pages[$i]['page']->getHeaderSize() +
                     $this->_currentPagePosition);
                break;
            }
            $streamSize += $this->_pages[$i]['page']->getPageSize();
        }
    }

    /**
     * Overwrite the method to jump <var>size</var> amount of bytes in the Ogg bitstream.
     * @param integer $size The amount of bytes to jump within the Ogg bitstream.
     * @throws \Vollbehr\Io\Exception if an I/O error occurs
     */
    public function skip($size): void
    {
        $currentPageSize = $this->_pages[$this->_currentPageNumber]['page']->getPageSize();
        if (($this->_currentPagePosition + $size) >= $currentPageSize) {
            parent::skip(($currentPageSize - $this->_currentPagePosition) +
                  $this->_pages[++$this->_currentPageNumber]['page']->getHeaderSize() +
                 ($this->_currentPagePosition = ($size - ($currentPageSize - $this->_currentPagePosition))));
        } else {
            $this->_currentPagePosition += $size;
            parent::skip($size);
        }
    }

    /**
     * Overwrite the method to read bytes within the Ogg bitstream.
     * @param integer $length The amount of bytes to read within the Ogg bitstream.
     * @throws \Vollbehr\Io\Exception if an I/O error occurs
     * @return string
     */
    public function read($length): string | false
    {
        $currentPageSize = $this->_pages[$this->_currentPageNumber]['page']->getPageSize();
        if (($this->_currentPagePosition + $length) >= $currentPageSize) {
            $buffer = parent::read($currentPageSize - $this->_currentPagePosition);
            parent::skip($this->_pages[++$this->_currentPageNumber]['page']->getHeaderSize());

            return $buffer . parent::read($this->_currentPagePosition = ($length - ($currentPageSize - $this->_currentPagePosition)));
        } else {
            $buffer = parent::read($length);
            $this->_currentPagePosition += $length;

            return $buffer;
        }
    }

    /**
     * Returns the underlying Ogg page at given number.
     * @param integer $pageNumber The number of the page to return.
     * @return Page
     */
    public function getPage($pageNumber)
    {
        return $this->_pages[$pageNumber]['page'];
    }

    /**
     * Returns the underlying Ogg page number.
     */
    public function getCurrentPageNumber(): int
    {
        return $this->_currentPageNumber;
    }

    /**
     * Returns the underlying Ogg page position, in bytes.
     * @return integer
     */
    public function getCurrentPagePosition(): float | int
    {
        return $this->_currentPagePosition;
    }
}
